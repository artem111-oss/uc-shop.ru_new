import { ref, type Ref } from 'vue'

export interface PaymentPayload {
  order_id: number
  amount: number
  game_id: string
}

export interface OrderPayload {
  game_id: string
  email: string | null
  product_id: number
}

export interface PaymentResponse {
  success: boolean
  link?: string
  uuid?: string
  payment_id?: string
  message?: string
  errors?: Record<string, string[]>
}

export interface OrderResponse {
  success: boolean
  id?: number
  uid?: string
  message?: string
  errors?: Record<string, string[]>
}

export function usePayment() {
  const isLoading: Ref<boolean> = ref(false)
  const error: Ref<string> = ref('')
  const successMessage: Ref<string> = ref('')

  const getCsrfToken = (): string => {
    const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.getAttribute('content')
    return token || ''
  }

    const createOrder = async (payload: OrderPayload): Promise<OrderResponse | null> => {
    isLoading.value = true
    error.value = ''

    try {
      const response = await fetch('/order/add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
          'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
      })

      const data: OrderResponse = await response.json()

      if (!response.ok || !data.success) {
        error.value = data.errors
          ? Object.values(data.errors).flat().join(', ')
          : 'Failed to create order'
        return null
      }

      return data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Network error'
      return null
    } finally {
      isLoading.value = false
    }
  }

  const initializePayment = async (payload: PaymentPayload): Promise<PaymentResponse | null> => {
    isLoading.value = true
    error.value = ''

    try {
      const response = await fetch('/order/payment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
          'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
      })

      const data: PaymentResponse = await response.json()

      if (!response.ok) {
        error.value = data.message || 'Payment initialization failed'
        return null
      }

      // OrderController@createPayment возвращает { link, payment_id, provider }
      if (!data.link) {
        error.value = 'Payment link not received'
        return null
      }

      successMessage.value = 'Payment initialized'
      return { ...data, success: true }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Network error'
      return null
    } finally {
      isLoading.value = false
    }
  }

  const processPayment = async (
    orderPayload: OrderPayload,
    paymentPayload: Omit<PaymentPayload, 'order_id'>
  ): Promise<string | null> => {
    const orderResponse = await createOrder(orderPayload)
    if (!orderResponse || !orderResponse.id) {
      return null
    }

    const paymentResponse = await initializePayment({
      ...paymentPayload,
      order_id: orderResponse.id,
    })

    if (!paymentResponse || !paymentResponse.link) {
      return null
    }

    return paymentResponse.link
  }

  return {
    isLoading,
    error,
    successMessage,
    createOrder,
    initializePayment,
    processPayment,
  }
}
