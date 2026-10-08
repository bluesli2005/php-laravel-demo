import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { expect } from 'storybook/test';
import BaseInput from './BaseInput.vue';

const meta = {
    title: 'Common/BaseInput',
    component: BaseInput,
    args: {
        id: 'base-input',
        modelValue: '',
        placeholder: '请输入内容',
        type: 'text',
    },
} satisfies Meta<typeof BaseInput>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Empty: Story = {};

export const Typing: Story = {
    play: async ({ canvas, userEvent }) => {
        const input = canvas.getByRole('textbox');

        await userEvent.type(input, '测试内容');
        await expect(input).toHaveValue('测试内容');
    },
};
