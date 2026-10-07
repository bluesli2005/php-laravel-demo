const configuredBaseUrl = import.meta.env.VITE_API_BASE_URL?.trim();

export const apiBaseUrl = (configuredBaseUrl || 'http://127.0.0.1:8000').replace(/\/$/, '');

interface ApiErrorBody {
    message?: unknown;
    errors?: unknown;
}

export class ApiError extends Error {
    public constructor(
        message: string,
        public readonly status: number,
        public readonly details: unknown = null,
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

export async function apiRequest(path: string, init: RequestInit = {}): Promise<unknown> {
    const headers = new Headers(init.headers);
    headers.set('Accept', 'application/json');

    if (init.body !== undefined && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }

    let response: Response;

    try {
        response = await fetch(`${apiBaseUrl}${path}`, {
            ...init,
            headers,
        });
    } catch (error) {
        throw new ApiError('Unable to reach the API.', 0, error);
    }

    const body = await parseResponseBody(response);

    if (!response.ok) {
        const errorBody = isRecord(body) ? (body as ApiErrorBody) : null;
        const message = typeof errorBody?.message === 'string' ? errorBody.message : `API request failed (${response.status}).`;

        throw new ApiError(message, response.status, errorBody?.errors ?? body);
    }

    return body;
}

async function parseResponseBody(response: Response): Promise<unknown> {
    const contentType = response.headers.get('content-type') ?? '';

    if (!contentType.includes('application/json')) {
        throw new ApiError('The API returned an invalid response format.', response.status);
    }

    try {
        return await response.json();
    } catch (error) {
        throw new ApiError('The API returned invalid JSON.', response.status, error);
    }
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}
