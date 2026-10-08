<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { ApiError } from '../api/client';
import BaseButton from './BaseButton.vue';
import BaseErrorMessage from './BaseErrorMessage.vue';
import BaseInput from './BaseInput.vue';
import BaseSelect from './BaseSelect.vue';
import BaseTextarea from './BaseTextarea.vue';
import {
    emptyPolicyPayload,
    policyStatuses,
    policyStatusLabels,
    type LifeInsurancePolicyPayload,
    type PolicyStatus,
} from '../types/lifeInsurancePolicy';

const props = withDefaults(defineProps<{
    initialValue?: LifeInsurancePolicyPayload;
    saving?: boolean;
    writesEnabled?: boolean;
}>(), {
    initialValue: undefined,
    saving: false,
    writesEnabled: true,
});

const emit = defineEmits<{
    submit: [payload: LifeInsurancePolicyPayload];
    cancel: [];
}>();

const form = reactive<LifeInsurancePolicyPayload>({
    ...emptyPolicyPayload(),
    ...props.initialValue,
});
const fieldErrors = ref<Record<string, string>>({});
const formError = ref('');
const statusOptions = policyStatuses.map((status) => ({
    value: status,
    label: policyStatusLabels[status],
}));

const isDisabled = computed(() => props.saving || !props.writesEnabled);

function submit(): void {
    fieldErrors.value = {};
    formError.value = '';

    const errors: Record<string, string> = {};
    const requiredFields: Array<[keyof LifeInsurancePolicyPayload, string]> = [
        ['policy_number', '保单号不能为空。'],
        ['policyholder_name', '投保人姓名不能为空。'],
        ['insured_name', '被保险人姓名不能为空。'],
        ['insured_birth_date', '被保险人出生日期不能为空。'],
        ['coverage_amount', '保额不能为空。'],
        ['premium_amount', '保费不能为空。'],
        ['effective_date', '生效日期不能为空。'],
    ];

    for (const [field, message] of requiredFields) {
        if (String(form[field]).trim() === '') {
            errors[field] = message;
        }
    }

    if (form.coverage_amount !== '' && Number(form.coverage_amount) <= 0) {
        errors.coverage_amount = '保额必须大于 0。';
    }

    if (form.premium_amount !== '' && Number(form.premium_amount) <= 0) {
        errors.premium_amount = '保费必须大于 0。';
    }

    if (form.expiry_date && form.effective_date && form.expiry_date !== '' && form.effective_date !== '' && form.expiry_date < form.effective_date) {
        errors.expiry_date = '失效日期不能早于生效日期。';
    }

    if (Object.keys(errors).length > 0) {
        fieldErrors.value = errors;
        return;
    }

    emit('submit', {
        ...form,
        beneficiary_name: form.beneficiary_name || null,
        expiry_date: form.expiry_date || null,
        notes: form.notes || null,
    });
}

function applyApiError(error: unknown): void {
    if (error instanceof ApiError && isRecord(error.details)) {
        const errors: Record<string, string> = {};

        for (const [field, messages] of Object.entries(error.details)) {
            if (Array.isArray(messages) && typeof messages[0] === 'string') {
                errors[field] = messages[0];
            }
        }

        if (Object.keys(errors).length > 0) {
            fieldErrors.value = errors;
            return;
        }
    }

    formError.value = error instanceof Error ? error.message : '保存失败，请稍后重试。';
}

function getFieldError(field: string): string | undefined {
    return fieldErrors.value[field];
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}

defineExpose({ applyApiError });
</script>

<template>
    <form class="grid gap-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
        <BaseErrorMessage v-if="formError" :message="formError" />

        <div class="grid gap-5 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-semibold">
                保单号
                <BaseInput v-model="form.policy_number" id="policy-number" :aria-invalid="!!getFieldError('policy_number')" maxlength="50" />
                <BaseErrorMessage :message="getFieldError('policy_number')" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                状态
                <BaseSelect v-model="form.status" id="policy-status" :options="statusOptions" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                投保人姓名
                <BaseInput v-model="form.policyholder_name" id="policyholder-name" :aria-invalid="!!getFieldError('policyholder_name')" maxlength="100" />
                <BaseErrorMessage :message="getFieldError('policyholder_name')" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                被保险人姓名
                <BaseInput v-model="form.insured_name" id="insured-name" :aria-invalid="!!getFieldError('insured_name')" maxlength="100" />
                <BaseErrorMessage :message="getFieldError('insured_name')" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                被保险人出生日期
                <BaseInput v-model="form.insured_birth_date" id="insured-birth-date" :aria-invalid="!!getFieldError('insured_birth_date')" type="date" />
                <BaseErrorMessage :message="getFieldError('insured_birth_date')" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                受益人姓名
                <BaseInput v-model="form.beneficiary_name" id="beneficiary-name" maxlength="100" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                保额
                <BaseInput v-model="form.coverage_amount" id="coverage-amount" :aria-invalid="!!getFieldError('coverage_amount')" min="0" step="0.01" type="number" />
                <BaseErrorMessage :message="getFieldError('coverage_amount')" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                保费
                <BaseInput v-model="form.premium_amount" id="premium-amount" :aria-invalid="!!getFieldError('premium_amount')" min="0" step="0.01" type="number" />
                <BaseErrorMessage :message="getFieldError('premium_amount')" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                币种
                <BaseInput v-model="form.currency" id="currency" maxlength="3" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                生效日期
                <BaseInput v-model="form.effective_date" id="effective-date" :aria-invalid="!!getFieldError('effective_date')" type="date" />
                <BaseErrorMessage :message="getFieldError('effective_date')" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                失效日期
                <BaseInput v-model="form.expiry_date" id="expiry-date" :aria-invalid="!!getFieldError('expiry_date')" type="date" />
                <BaseErrorMessage :message="getFieldError('expiry_date')" />
            </label>
        </div>

        <label class="grid gap-2 text-sm font-semibold">
            备注
            <BaseTextarea v-model="form.notes" id="policy-notes" :aria-invalid="!!getFieldError('notes')" maxlength="1000" />
            <BaseErrorMessage :message="getFieldError('notes')" />
        </label>

        <div class="flex flex-wrap gap-3">
            <BaseButton :disabled="isDisabled" type="submit">
                {{ saving ? '保存中…' : '保存保单' }}
            </BaseButton>
            <BaseButton :disabled="saving" type="button" variant="secondary" @click="emit('cancel')">取消</BaseButton>
        </div>
    </form>
</template>
