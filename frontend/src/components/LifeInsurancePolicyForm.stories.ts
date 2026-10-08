import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { expect, fn } from 'storybook/test';
import LifeInsurancePolicyForm from './LifeInsurancePolicyForm.vue';
import { emptyPolicyPayload } from '../types/lifeInsurancePolicy';

const meta = {
    title: 'Insurance/LifeInsurancePolicyForm',
    component: LifeInsurancePolicyForm,
    args: {
        initialValue: emptyPolicyPayload(),
        saving: false,
        writesEnabled: true,
        onSubmit: fn(),
        onCancel: fn(),
    },
    parameters: {
        a11y: {
            test: 'error',
        },
    },
} satisfies Meta<typeof LifeInsurancePolicyForm>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Empty: Story = {};

export const Validation: Story = {
    play: async ({ canvas, userEvent }) => {
        await userEvent.click(canvas.getByRole('button', { name: '保存保单' }));

        await expect(canvas.getByText('保单号不能为空。')).toBeInTheDocument();
        await expect(canvas.getByText('投保人姓名不能为空。')).toBeInTheDocument();
        await expect(canvas.getByText('被保险人姓名不能为空。')).toBeInTheDocument();
    },
};
