import { afterEach, describe, expect, it, vi } from 'vitest';
import { ApiError } from './client';
import {
    createWelcomeMessage,
    deleteWelcomeMessage,
    updateWelcomeMessage,
} from './welcomeMessages';

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('welcomeMessages API client', () => {
    it('creates content with the expected page and JSON body', async () => {
        const fetchMock = mockJsonResponse({
            data: { page: 'services', content: 'Created content.' },
        }, 201);

        await expect(createWelcomeMessage('services', 'Created content.')).resolves.toEqual({
            page: 'services',
            content: 'Created content.',
        });
        expect(fetchMock).toHaveBeenCalledWith(
            'http://127.0.0.1:8000/api/v1/welcome-messages',
            expect.objectContaining({
                method: 'POST',
                body: JSON.stringify({ page: 'services', content: 'Created content.' }),
            }),
        );

        const request = fetchMock.mock.calls[0]?.[1] as RequestInit;
        const headers = request.headers as Headers;
        expect(headers.get('Accept')).toBe('application/json');
        expect(headers.get('Content-Type')).toBe('application/json');
    });

    it('updates content with PATCH and returns the validated response', async () => {
        const fetchMock = mockJsonResponse({
            data: { page: 'about', content: 'Updated content.' },
        });

        await expect(updateWelcomeMessage('about', 'Updated content.')).resolves.toEqual({
            page: 'about',
            content: 'Updated content.',
        });
        expect(fetchMock).toHaveBeenCalledWith(
            'http://127.0.0.1:8000/api/v1/welcome-messages/about',
            expect.objectContaining({
                method: 'PATCH',
                body: JSON.stringify({ content: 'Updated content.' }),
            }),
        );
    });

    it('deletes the requested page with DELETE', async () => {
        const fetchMock = mockJsonResponse({
            data: { page: 'contact', content: 'Deleted content.' },
        });

        await expect(deleteWelcomeMessage('contact')).resolves.toEqual({
            page: 'contact',
            content: 'Deleted content.',
        });
        expect(fetchMock).toHaveBeenCalledWith(
            'http://127.0.0.1:8000/api/v1/welcome-messages/contact',
            expect.objectContaining({ method: 'DELETE' }),
        );
    });

    it('rejects a response for a different page', async () => {
        mockJsonResponse({
            data: { page: 'home', content: 'Wrong page.' },
        });

        await expect(updateWelcomeMessage('about', 'Updated content.')).rejects.toThrow(
            'The API returned content for home instead of about.',
        );
    });

    it('preserves backend validation errors for the form', async () => {
        mockJsonResponse(
            {
                message: 'The given data was invalid.',
                errors: {
                    content: ['The content field is required.'],
                },
            },
            422,
        );

        const error = await updateWelcomeMessage('home', '').catch((caught: unknown) => caught);

        expect(error).toBeInstanceOf(ApiError);
        expect(error).toMatchObject({
            status: 422,
            details: {
                content: ['The content field is required.'],
            },
        });
    });
});

function mockJsonResponse(body: unknown, status = 200): ReturnType<typeof vi.fn> {
    const fetchMock = vi.fn().mockResolvedValue(
        new Response(JSON.stringify(body), {
            status,
            headers: { 'Content-Type': 'application/json' },
        }),
    );
    vi.stubGlobal('fetch', fetchMock);

    return fetchMock;
}
