import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { expect } from 'storybook/test';
import PolicyStatusSelect from './PolicyStatusSelect.vue';

const meta = {
    title: 'Insurance/PolicyStatusSelect',
    component: PolicyStatusSelect,
    args: {
        modelValue: '',
    },
} satisfies Meta<typeof PolicyStatusSelect>;

export default meta;

type Story = StoryObj<typeof meta>;

export const All: Story = {};

export const Active: Story = {
    args: {
        modelValue: 'active',
    },
    play: async ({ canvas }) => {
        await expect(canvas.getByRole('combobox')).toHaveValue('active');
    },
};
