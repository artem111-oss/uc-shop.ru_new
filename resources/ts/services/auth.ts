export interface AuthUser {
  id: number;
  name: string;
  email: string;
}

export interface OrderSummary {
  id: number;
  created_at: string | null;
  completed_at: string | null;
  price: number;
  currency: string;
  status: {
    code: string;
    label: string;
  };
  product: {
    id: number;
    name: string | null;
  };
  pubg_id: string | null;
  uc_amount: string | null;
}

export interface OrdersResponse {
  data: OrderSummary[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

const TOKEN_KEY = 'uc_shop_customer_token';

export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken(): void {
  localStorage.removeItem(TOKEN_KEY);
}

function authHeaders(): Record<string, string> {
  const token = getToken();
  return token ? { Authorization: `Bearer ${token}` } : {};
}

export async function requestLoginCode(email: string): Promise<{ message: string }> {
  const response = await fetch('/api/auth/request-code', {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email }),
  });

  const body = await response.json();

  if (!response.ok) {
    throw new Error(body.message || 'Не удалось запросить код.');
  }

  return body;
}

export async function verifyLoginCode(email: string, code: string): Promise<{ user: AuthUser; token: string }> {
  const response = await fetch('/api/auth/verify-code', {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, code, device_name: 'web' }),
  });

  const body = await response.json();

  if (!response.ok) {
    throw new Error(body.message || 'Код неверен или истёк.');
  }

  setToken(body.token);

  return body;
}

export async function fetchCurrentUser(): Promise<AuthUser | null> {
  const token = getToken();

  if (!token) {
    return null;
  }

  const response = await fetch('/api/auth/me', {
    headers: {
      Accept: 'application/json',
      ...authHeaders(),
    },
  });

  if (response.status === 401) {
    clearToken();
    return null;
  }

  if (!response.ok) {
    return null;
  }

  const body = await response.json();
  return body.user as AuthUser;
}

export async function fetchOrders(page: number): Promise<OrdersResponse> {
  const response = await fetch(`/api/account/orders?page=${page}`, {
    headers: {
      Accept: 'application/json',
      ...authHeaders(),
    },
  });

  if (!response.ok) {
    throw new Error('Не удалось загрузить историю заказов.');
  }

  return response.json();
}

export async function logout(): Promise<void> {
  const token = getToken();

  if (!token) {
    return;
  }

  await fetch('/api/auth/logout', {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      ...authHeaders(),
    },
  });

  clearToken();
}

export function isAuthenticated(): boolean {
  return !!getToken();
}

export function authorizationHeader(): Record<string, string> {
  return authHeaders();
}