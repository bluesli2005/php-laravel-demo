import { apiRequest } from './client';
import type {
    LifeInsurancePolicy,
    LifeInsurancePolicyPayload,
    PolicyStatus,
} from '../types/lifeInsurancePolicy';

export interface LifeInsurancePolicyFilters {
    search?: string;
    status?: PolicyStatus | '';
    page?: number;
    perPage?: number;
}

export interface LifeInsurancePolicyPage {
    policies: LifeInsurancePolicy[];
    currentPage: number;
    lastPage: number;
    total: number;
}

export async function getLifeInsurancePolicies(
    filters: LifeInsurancePolicyFilters = {},
    signal?: AbortSignal,
): Promise<LifeInsurancePolicyPage> {
    const params = new URLSearchParams();

    if (filters.search?.trim() !== '') {
        params.set('search', filters.search?.trim() ?? '');
    }

    if (filters.status !== undefined && filters.status !== '') {
        params.set('status', filters.status);
    }

    if (filters.page !== undefined) {
        params.set('page', String(filters.page));
    }

    if (filters.perPage !== undefined) {
        params.set('per_page', String(filters.perPage));
    }

    const query = params.toString();
    return parsePolicyPage(await apiRequest(`/api/v1/life-insurance-policies${query ? `?${query}` : ''}`, {
        cache: 'no-store',
        signal,
    }));
}

function parsePolicyPage(value: unknown): LifeInsurancePolicyPage {
    if (!isRecord(value) || !Array.isArray(value.data) || !isRecord(value.meta)) {
        throw new TypeError('The policy list has an invalid response format.');
    }

    const { current_page: currentPage, last_page: lastPage, total } = value.meta;

    if (typeof currentPage !== 'number' || typeof lastPage !== 'number' || typeof total !== 'number') {
        throw new TypeError('The policy list has invalid pagination data.');
    }

    return {
        policies: value.data.map(parsePolicy),
        currentPage,
        lastPage,
        total,
    };
}

export async function getLifeInsurancePolicy(id: number, signal?: AbortSignal): Promise<LifeInsurancePolicy> {
    return parsePolicy(getEnvelopeData(await apiRequest(`/api/v1/life-insurance-policies/${id}`, {
        cache: 'no-store',
        signal,
    })));
}

export async function createLifeInsurancePolicy(
    payload: LifeInsurancePolicyPayload,
    signal?: AbortSignal,
): Promise<LifeInsurancePolicy> {
    return parsePolicy(getEnvelopeData(await apiRequest('/api/v1/life-insurance-policies', {
        method: 'POST',
        body: JSON.stringify(payload),
        signal,
    })));
}

export async function updateLifeInsurancePolicy(
    id: number,
    payload: Partial<LifeInsurancePolicyPayload>,
    signal?: AbortSignal,
): Promise<LifeInsurancePolicy> {
    return parsePolicy(getEnvelopeData(await apiRequest(`/api/v1/life-insurance-policies/${id}`, {
        method: 'PATCH',
        body: JSON.stringify(payload),
        signal,
    })));
}

export async function deleteLifeInsurancePolicy(id: number, signal?: AbortSignal): Promise<LifeInsurancePolicy> {
    return parsePolicy(getEnvelopeData(await apiRequest(`/api/v1/life-insurance-policies/${id}`, {
        method: 'DELETE',
        signal,
    })));
}

function getEnvelopeData(value: unknown): unknown {
    if (!isRecord(value) || !Object.hasOwn(value, 'data')) {
        throw new TypeError('The API response does not contain a data field.');
    }

    return value.data;
}

function parsePolicy(value: unknown): LifeInsurancePolicy {
    if (
        !isRecord(value) ||
        typeof value.id !== 'number' ||
        typeof value.policy_number !== 'string' ||
        typeof value.policyholder_name !== 'string' ||
        typeof value.insured_name !== 'string' ||
        typeof value.insured_birth_date !== 'string' ||
        !isNullableString(value.beneficiary_name) ||
        !isDecimal(value.coverage_amount) ||
        !isDecimal(value.premium_amount) ||
        typeof value.currency !== 'string' ||
        !isPolicyStatus(value.status) ||
        typeof value.effective_date !== 'string' ||
        !isNullableString(value.expiry_date) ||
        !isNullableString(value.notes) ||
        typeof value.created_at !== 'string' ||
        typeof value.updated_at !== 'string'
    ) {
        throw new TypeError('The API returned an invalid life-insurance policy.');
    }

    return value as unknown as LifeInsurancePolicy;
}

function isDecimal(value: unknown): value is string {
    return typeof value === 'string';
}

function isNullableString(value: unknown): value is string | null {
    return value === null || typeof value === 'string';
}

function isPolicyStatus(value: unknown): value is PolicyStatus {
    return value === 'draft' || value === 'active' || value === 'expired' || value === 'cancelled';
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}
