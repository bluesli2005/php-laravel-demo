import { flushPromises, mount, type VueWrapper } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { ApiError } from '../api/client';
import {
    createWelcomeMessage,
    deleteWelcomeMessage,
    getWelcomeMessage,
    updateWelcomeMessage,
} from '../api/welcomeMessages';
import WelcomeMessagePage from './WelcomeMessagePage.vue';

vi.mock('../api/welcomeMessages', () => ({
    createWelcomeMessage: vi.fn(),
    deleteWelcomeMessage: vi.fn(),
    getWelcomeMessage: vi.fn(),
    updateWelcomeMessage: vi.fn(),
}));

const existingMessage = {
    page: 'home' as const,
    content: 'Original content.',
};

beforeEach(() => {
    vi.resetAllMocks();
});

describe('WelcomeMessagePage', () => {
    it('shows a loading state until the API responds', () => {
        vi.mocked(getWelcomeMessage).mockReturnValue(new Promise(() => undefined));

        const wrapper = mountPage();

        expect(wrapper.text()).toContain('Loading page content.');
        wrapper.unmount();
    });

    it('loads and displays the page content', async () => {
        const wrapper = await mountLoadedPage();

        expect(wrapper.text()).toContain('Original content.');
        expect(wrapper.get('h1').text()).toBe('Home');
    });

    it('updates content without a page refresh', async () => {
        const wrapper = await mountLoadedPage();
        vi.mocked(updateWelcomeMessage).mockResolvedValue({
            page: 'home',
            content: 'Updated content.',
        });

        await findButton(wrapper, 'Edit content').trigger('click');
        await wrapper.get('textarea').setValue('Updated content.');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(updateWelcomeMessage).toHaveBeenCalledWith('home', 'Updated content.', expect.any(AbortSignal));
        expect(wrapper.text()).toContain('Updated content.');
        expect(wrapper.text()).toContain('Content saved.');
        expect(wrapper.find('textarea').exists()).toBe(false);
    });

    it('rejects empty content before calling the API', async () => {
        const wrapper = await mountLoadedPage();

        await findButton(wrapper, 'Edit content').trigger('click');
        await wrapper.get('textarea').setValue('   ');
        await wrapper.get('form').trigger('submit');

        expect(wrapper.text()).toContain('Content is required.');
        expect(updateWelcomeMessage).not.toHaveBeenCalled();
    });

    it('rejects content over the database limit before calling the API', async () => {
        const wrapper = await mountLoadedPage();

        await findButton(wrapper, 'Edit content').trigger('click');
        await wrapper.get('textarea').setValue('a'.repeat(256));
        await wrapper.get('form').trigger('submit');

        expect(wrapper.text()).toContain('Content must not be greater than 255 characters.');
        expect(updateWelcomeMessage).not.toHaveBeenCalled();
    });

    it('displays backend validation errors beside the field', async () => {
        const wrapper = await mountLoadedPage();
        vi.mocked(updateWelcomeMessage).mockRejectedValue(
            new ApiError('The given data was invalid.', 422, {
                content: ['The content field must not be greater than 255 characters.'],
            }),
        );

        await findButton(wrapper, 'Edit content').trigger('click');
        await wrapper.get('textarea').setValue('Updated content.');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(wrapper.text()).toContain('The content field must not be greater than 255 characters.');
        expect(wrapper.get('textarea').attributes('aria-invalid')).toBe('true');
    });

    it('explains when writing is disabled by the backend', async () => {
        const wrapper = await mountLoadedPage();
        vi.mocked(updateWelcomeMessage).mockRejectedValue(new ApiError('Forbidden.', 403));

        await findButton(wrapper, 'Edit content').trigger('click');
        await wrapper.get('textarea').setValue('Updated content.');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(wrapper.text()).toContain('Writing is disabled in this environment.');
    });

    it('requires confirmation before deleting and then shows the recreate action', async () => {
        const wrapper = await mountLoadedPage();
        vi.mocked(deleteWelcomeMessage).mockResolvedValue(existingMessage);

        await findButton(wrapper, 'Delete content').trigger('click');

        expect(wrapper.text()).toContain('Delete this page content?');
        expect(deleteWelcomeMessage).not.toHaveBeenCalled();

        await findButton(wrapper, 'Delete permanently').trigger('click');
        await flushPromises();

        expect(deleteWelcomeMessage).toHaveBeenCalledWith('home', expect.any(AbortSignal));
        expect(wrapper.text()).toContain('Content deleted. You can recreate it below.');
        expect(wrapper.text()).toContain('Create content');
    });

    it('creates missing content and displays it immediately', async () => {
        vi.mocked(getWelcomeMessage).mockRejectedValue(new ApiError('Not found.', 404));
        vi.mocked(createWelcomeMessage).mockResolvedValue({
            page: 'home',
            content: 'Recreated content.',
        });
        const wrapper = mountPage();
        await flushPromises();

        await findButton(wrapper, 'Create content').trigger('click');
        await wrapper.get('textarea').setValue('Recreated content.');
        await wrapper.get('form').trigger('submit');
        await flushPromises();

        expect(createWelcomeMessage).toHaveBeenCalledWith('home', 'Recreated content.', expect.any(AbortSignal));
        expect(wrapper.text()).toContain('Recreated content.');
        expect(wrapper.text()).toContain('Content saved.');
    });

    it('hides write controls in read-only environments', async () => {
        vi.mocked(getWelcomeMessage).mockResolvedValue(existingMessage);
        const wrapper = mountPage(false);
        await flushPromises();

        expect(wrapper.text()).toContain('Original content.');
        expect(wrapper.text()).not.toContain('Edit content');
        expect(wrapper.text()).not.toContain('Delete content');
    });
});

function mountPage(writesEnabled = true): VueWrapper {
    return mount(WelcomeMessagePage, {
        props: {
            page: 'home',
            title: 'Home',
            writesEnabled,
        },
    });
}

async function mountLoadedPage(): Promise<VueWrapper> {
    vi.mocked(getWelcomeMessage).mockResolvedValue(existingMessage);
    const wrapper = mountPage();
    await flushPromises();

    return wrapper;
}

function findButton(wrapper: VueWrapper, label: string): ReturnType<VueWrapper['get']> {
    const button = wrapper.findAll('button').find((candidate) => candidate.text() === label);

    if (button === undefined) {
        throw new Error(`Button not found: ${label}`);
    }

    return button;
}
