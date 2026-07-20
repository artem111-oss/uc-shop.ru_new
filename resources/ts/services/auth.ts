export interface AuthUser {
  id: number;
  name: string;
  email: string;
  notify_email?: boolean;
  notify_telegram?: boolean;
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

export interface PubgAccount {
  id: number;
  pubg_id: string;
  nickname: string | null;
  is_primary: boolean;
  created_at: string | null;
}

export interface TelegramLinkStatus {
  bot: string;
  telegram_username: string | null;
  linked_at: string | null;
}

const TOKEN_KEY = 'uc_shop_customer_token';
const PRIMARY_PUBG_ID_KEY = 'uc_shop_primary_pubg_id';

export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken(): void {
  localStorage.removeItem(TOKEN_KEY);
}

export function getStoredPrimaryPubgId(): string | null {
  return localStorage.getItem(PRIMARY_PUBG_ID_KEY);
}

export function setStoredPrimaryPubgId(pubgId: string | null): void {
  if (pubgId && pubgId.trim()) {
    localStorage.setItem(PRIMARY_PUBG_ID_KEY, pubgId.trim());
    return;
  }

  localStorage.removeItem(PRIMARY_PUBG_ID_KEY);
}

export async function fetchPrimaryPubgId(): Promise<string | null> {
  if (!getToken()) {
    return getStoredPrimaryPubgId();
  }

  const accounts = await fetchPubgAccounts();
  const primary = accounts.find((account) => account.is_primary) ?? accounts[0] ?? null;
  const pubgId = primary?.pubg_id ?? null;

  setStoredPrimaryPubgId(pubgId);

  return pubgId;
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

async function authorizedFetch(url: string, options: RequestInit = {}): Promise<Response> {
  const response = await fetch(url, {
    ...options,
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...authHeaders(),
      ...(options.headers || {}),
    },
  });

  if (response.status === 401) {
    clearToken();
  }

  return response;
}

async function parseOrThrow(response: Response, fallbackMessage: string): Promise<any> {
  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(body.message || fallbackMessage);
  }

  return body;
}

export async function fetchPubgAccounts(): Promise<PubgAccount[]> {
  const response = await authorizedFetch('/api/account/pubg-accounts');
  const body = await parseOrThrow(response, 'Не удалось загрузить игровые аккаунты.');
  return body.data as PubgAccount[];
}

export async function createPubgAccount(pubgId: string, nickname: string): Promise<PubgAccount> {
  const response = await authorizedFetch('/api/account/pubg-accounts', {
    method: 'POST',
    body: JSON.stringify({ pubg_id: pubgId, nickname: nickname || null }),
  });
  const body = await parseOrThrow(response, 'Не удалось добавить PUBG ID.');
  return body.data as PubgAccount;
}

export async function updatePubgAccount(
  id: number,
  data: { nickname?: string | null; is_primary?: boolean }
): Promise<PubgAccount> {
  const response = await authorizedFetch(`/api/account/pubg-accounts/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(data),
  });
  const body = await parseOrThrow(response, 'Не удалось обновить PUBG ID.');
  return body.data as PubgAccount;
}

export async function deletePubgAccount(id: number): Promise<void> {
  const response = await authorizedFetch(`/api/account/pubg-accounts/${id}`, {
    method: 'DELETE',
  });
  await parseOrThrow(response, 'Не удалось удалить PUBG ID.');
}

export async function fetchNotificationPrefs(): Promise<{ notify_email: boolean; notify_telegram: boolean }> {
  const response = await authorizedFetch('/api/auth/me');
  const body = await parseOrThrow(response, 'Не удалось загрузить настройки уведомлений.');
  return {
    notify_email: !!body.user?.notify_email,
    notify_telegram: !!body.user?.notify_telegram,
  };
}

export async function updateNotificationPrefs(
  data: { notify_email?: boolean; notify_telegram?: boolean }
): Promise<void> {
  const response = await authorizedFetch('/api/account/notifications', {
    method: 'PATCH',
    body: JSON.stringify(data),
  });
  await parseOrThrow(response, 'Не удалось сохранить настройки уведомлений.');
}

export async function fetchTelegramStatus(): Promise<TelegramLinkStatus[]> {
  const response = await authorizedFetch('/api/account/telegram/status');
  const body = await parseOrThrow(response, 'Не удалось загрузить статус Telegram.');
  return body.data as TelegramLinkStatus[];
}

export interface TelegramWidgetAuthPayload {
  id: number;
  first_name?: string;
  last_name?: string;
  username?: string;
  photo_url?: string;
  auth_date: number;
  hash: string;
}

export async function linkTelegramWidget(payload: TelegramWidgetAuthPayload): Promise<TelegramLinkStatus> {
  const response = await authorizedFetch('/api/account/telegram/widget-link', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
  const body = await parseOrThrow(response, 'Не удалось привязать Telegram.');
  return body.data as TelegramLinkStatus;
}

export async function unlinkTelegram(bot: string): Promise<void> {
  const response = await authorizedFetch(`/api/account/telegram/${bot}`, {
    method: 'DELETE',
  });
  await parseOrThrow(response, 'Не удалось отключить Telegram.');
}