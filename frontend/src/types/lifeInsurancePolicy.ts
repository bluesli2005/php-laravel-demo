export const policyStatuses = ['draft', 'active', 'expired', 'cancelled'] as const;

export type PolicyStatus = (typeof policyStatuses)[number];

export interface LifeInsurancePolicy {
    id: number;
    policy_number: string;
    policyholder_name: string;
    insured_name: string;
    insured_birth_date: string;
    beneficiary_name: string | null;
    coverage_amount: string;
    premium_amount: string;
    currency: string;
    status: PolicyStatus;
    effective_date: string;
    expiry_date: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

export interface LifeInsurancePolicyPayload {
    policy_number: string;
    policyholder_name: string;
    insured_name: string;
    insured_birth_date: string;
    beneficiary_name: string | null;
    coverage_amount: string;
    premium_amount: string;
    currency: string;
    status: PolicyStatus;
    effective_date: string;
    expiry_date: string | null;
    notes: string | null;
}

export type PolicyFormState = 'loading' | 'error' | 'ready';

export const policyStatusLabels: Record<PolicyStatus, string> = {
    draft: '草稿',
    active: '生效中',
    expired: '已失效',
    cancelled: '已取消',
};

export function emptyPolicyPayload(): LifeInsurancePolicyPayload {
    return {
        policy_number: '',
        policyholder_name: '',
        insured_name: '',
        insured_birth_date: '',
        beneficiary_name: '',
        coverage_amount: '',
        premium_amount: '',
        currency: 'CNY',
        status: 'draft',
        effective_date: '',
        expiry_date: '',
        notes: '',
    };
}

export function policyToPayload(policy: LifeInsurancePolicy): LifeInsurancePolicyPayload {
    return {
        policy_number: policy.policy_number,
        policyholder_name: policy.policyholder_name,
        insured_name: policy.insured_name,
        insured_birth_date: policy.insured_birth_date,
        beneficiary_name: policy.beneficiary_name ?? '',
        coverage_amount: policy.coverage_amount,
        premium_amount: policy.premium_amount,
        currency: policy.currency,
        status: policy.status,
        effective_date: policy.effective_date,
        expiry_date: policy.expiry_date ?? '',
        notes: policy.notes ?? '',
    };
}