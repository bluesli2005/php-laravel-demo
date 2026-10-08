import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { expect } from 'storybook/test';
import BaseSelect from './BaseSelect.vue';

const meta = {
    title: 'Common/BaseSelect',
    component: BaseSelect,
    args: {
        id: 'base-select',
        modelValue: '',
        ariaLabel: '保险状态',
        options: [
            { value: '', label: '全部' },
            { value: 'active', label: '生效中' },
            { value: 'expired', label: '已失效' },
        ],
    },
} satisfies Meta<typeof BaseSelect>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const SelectActive: Story = {
    play: async ({ canvas, userEvent }) => {
        const select = canvas.getByRole('combobox');

        await userEvent.selectOptions(select, 'active');
        await expect(select).toHaveValue('active');
    },
};
