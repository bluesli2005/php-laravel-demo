import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { expect } from 'storybook/test';
import PolicySearchBox from './PolicySearchBox.vue';

const meta = {
    title: 'Insurance/PolicySearchBox',
    component: PolicySearchBox,
    args: {
        modelValue: '',
        placeholder: '搜索保单号或姓名',
    },
} satisfies Meta<typeof PolicySearchBox>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Empty: Story = {};

export const SearchText: Story = {
    play: async ({ canvas, userEvent }) => {
        const input = canvas.getByRole('searchbox');

        await userEvent.type(input, '张三');
        await expect(input).toHaveValue('张三');
    },
};
