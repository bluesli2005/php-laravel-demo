import type { Meta, StoryObj } from '@storybook/vue3-vite';
import BaseErrorMessage from './BaseErrorMessage.vue';

const meta = {
    title: '基础控件/BaseErrorMessage',
    component: BaseErrorMessage,
    tags: ['autodocs'],
} satisfies Meta<typeof BaseErrorMessage>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        message: '请输入有效内容。',
    },
};
