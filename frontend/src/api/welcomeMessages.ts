import { apiRequest } from './client';
import { pageNames, type PageName, type WelcomeMessage } from '../types/welcomeMessage';

export async function getWelcomeMessages(): Promise<WelcomeMessage[]> {
    const body = await apiRequest('/api/v1/welcome-messages');
    const data = getEnvelopeData(body);

    if (!Array.isArray(data)) {
        throw new TypeError('The welcome-message list has an invalid data field.');
    }

    return data.map(parseWelcomeMessage);
}

export async function getWelcomeMessage(page: PageName, signal?: AbortSignal): Promise<WelcomeMessage> {
    const body = await apiRequest(`/api/v1/welcome-messages/${page}`, {
        cache: 'no-store',
        signal,
    });
    const message = parseWelcomeMessage(getEnvelopeData(body));

    if (message.page !== page) {
        throw new TypeError(`The API returned content for ${message.page} instead of ${page}.`);
    }

    return message;
}

function getEnvelopeData(value: unknown): unknown {
    if (!isRecord(value) || !Object.hasOwn(value, 'data')) {
        throw new TypeError('The API response does not contain a data field.');
    }

    return value.data;
}

function parseWelcomeMessage(value: unknown): WelcomeMessage {
    if (
        !isRecord(value) ||
        !isPageName(value.page) ||
        typeof value.content !== 'string'
    ) {
        throw new TypeError('The API returned an invalid welcome message.');
    }

    return {
        page: value.page,
        content: value.content,
    };
}

function isPageName(value: unknown): value is PageName {
    return typeof value === 'string' && pageNames.some((page) => page === value);
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}
