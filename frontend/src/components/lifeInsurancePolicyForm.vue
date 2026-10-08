<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { ApiError } from '../api/client';
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
        <div v-if="formError" class="rounded-md bg-red-50 p-3 text-sm text-red-800" role="alert">
            {{ formError }}
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-semibold">
                保单号
                <input v-model="form.policy_number" :aria-invalid="!!getFieldError('policy_number')" class="form-input" maxlength="50" />
                <span v-if="getFieldError('policy_number')" class="font-normal text-red-700">{{ getFieldError('policy_number') }}</span>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                状态
                <select v-model="form.status" class="form-input">
                    <option v-for="status in policyStatuses" :key="status" :value="status">{{ policyStatusLabels[status] }}</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                投保人姓名
                <input v-model="form.policyholder_name" :aria-invalid="!!getFieldError('policyholder_name')" class="form-input" maxlength="100" />
                <span v-if="getFieldError('policyholder_name')" class="font-normal text-red-700">{{ getFieldError('policyholder_name') }}</span>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                被保险人姓名
                <input v-model="form.insured_name" :aria-invalid="!!getFieldError('insured_name')" class="form-input" maxlength="100" />
                <span v-if="getFieldError('insured_name')" class="font-normal text-red-700">{{ getFieldError('insured_name') }}</span>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                被保险人出生日期
                <input v-model="form.insured_birth_date" :aria-invalid="!!getFieldError('insured_birth_date')" class="form-input" type="date" />
                <span v-if="getFieldError('insured_birth_date')" class="font-normal text-red-700">{{ getFieldError('insured_birth_date') }}</span>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                受益人姓名
                <input v-model="form.beneficiary_name" class="form-input" maxlength="100" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                保额
                <input v-model="form.coverage_amount" :aria-invalid="!!getFieldError('coverage_amount')" class="form-input" min="0" step="0.01" type="number" />
                <span v-if="getFieldError('coverage_amount')" class="font-normal text-red-700">{{ getFieldError('coverage_amount') }}</span>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                保费
                <input v-model="form.premium_amount" :aria-invalid="!!getFieldError('premium_amount')" class="form-input" min="0" step="0.01" type="number" />
                <span v-if="getFieldError('premium_amount')" class="font-normal text-red-700">{{ getFieldError('premium_amount') }}</span>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                币种
                <input v-model="form.currency" class="form-input uppercase" maxlength="3" />
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                生效日期
                <input v-model="form.effective_date" :aria-invalid="!!getFieldError('effective_date')" class="form-input" type="date" />
                <span v-if="getFieldError('effective_date')" class="font-normal text-red-700">{{ getFieldError('effective_date') }}</span>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                失效日期
                <input v-model="form.expiry_date" :aria-invalid="!!getFieldError('expiry_date')" class="form-input" type="date" />
                <span v-if="getFieldError('expiry_date')" class="font-normal text-red-700">{{ getFieldError('expiry_date') }}</span>
            </label>
        </div>

        <label class="grid gap-2 text-sm font-semibold">
            备注
            <textarea v-model="form.notes" :aria-invalid="!!getFieldError('notes')" class="form-input min-h-28" maxlength="1000"></textarea>
            <span v-if="getFieldError('notes')" class="font-normal text-red-700">{{ getFieldError('notes') }}</span>
        </label>

        <div class="flex flex-wrap gap-3">
            <button :disabled="isDisabled" class="button-primary" type="submit">
                {{ saving ? '保存中…' : '保存保单' }}
            </button>
            <button :disabled="saving" class="button-secondary" type="button" @click="emit('cancel')">取消</button>
        </div>
    </form>
</template>