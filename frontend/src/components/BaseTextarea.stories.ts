import type { Meta, StoryObj } from '@storybook/vue3-vite';
import BaseTextarea from './BaseTextarea.vue';

const meta = {
    title: '基础控件/BaseTextarea',
    component: BaseTextarea,
    tags: ['autodocs'],
} satisfies Meta<typeof BaseTextarea>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        id: 'notes',
        modelValue: '',
        maxlength: 1000,
    },
    render: (args) => ({
        components: { BaseTextarea },
        setup: () => ({ args }),
        template: '<label for="notes">备注<BaseTextarea v-bind="args" /></label>',
    }),
};
