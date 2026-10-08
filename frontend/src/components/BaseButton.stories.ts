import type { Meta, StoryObj } from '@storybook/vue3-vite';
import BaseButton from './BaseButton.vue';

const meta = {
    title: '基础控件/BaseButton',
    component: BaseButton,
    tags: ['autodocs'],
} satisfies Meta<typeof BaseButton>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Primary: Story = {
    args: {
        variant: 'primary',
    },
    render: (args) => ({
        components: { BaseButton },
        setup: () => ({ args }),
        template: '<BaseButton v-bind="args">保存</BaseButton>',
    }),
};

export const Secondary: Story = {
    args: {
        variant: 'secondary',
    },
    render: (args) => ({
        components: { BaseButton },
        setup: () => ({ args }),
        template: '<BaseButton v-bind="args">取消</BaseButton>',
    }),
};
