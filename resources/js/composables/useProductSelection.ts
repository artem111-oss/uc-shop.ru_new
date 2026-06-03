import { ref, computed, type Ref, type ComputedRef } from 'vue'

export interface Product {
  id: number
  name: string
  price: number
  bonus?: string
  popular?: boolean
}

export function useProductSelection(products: Product[]) {
  const selectedProduct: Ref<Product | null> = ref(null)
  const focusedProductId: Ref<number | null> = ref(null)

  const selectedPrice: ComputedRef<number> = computed(() => selectedProduct.value?.price ?? 0)
  const selectedName: ComputedRef<string> = computed(() => selectedProduct.value?.name ?? '')
  const hasSelection: ComputedRef<boolean> = computed(() => selectedProduct.value !== null)

  const selectProduct = (product: Product): void => {
    selectedProduct.value = product
  }

  const clearSelection = (): void => {
    selectedProduct.value = null
  }

  const isSelected = (productId: number): boolean => {
    return selectedProduct.value?.id === productId
  }

  const isFocused = (productId: number): boolean => {
    return focusedProductId.value === productId
  }

  const setFocused = (productId: number | null): void => {
    focusedProductId.value = productId
  }

  const selectDefault = (index: number = 0): void => {
    if (products.length > index) {
      selectedProduct.value = products[index]
    }
  }

  return {
    selectedProduct,
    focusedProductId,
    selectedPrice,
    selectedName,
    hasSelection,
    selectProduct,
    clearSelection,
    isSelected,
    isFocused,
    setFocused,
    selectDefault,
  }
}
