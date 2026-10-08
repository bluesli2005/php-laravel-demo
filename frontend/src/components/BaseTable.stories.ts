import type { Meta, StoryObj } from '@storybook/vue3-vite';
import BaseTable from './BaseTable.vue';

const meta = {
    title: 'Common/BaseTable',
    component: BaseTable,
    args: {
        rowKey: 'id',
        columns: [
            { key: 'name', label: '姓名' },
            { key: 'status', label: '状态' },
        ],
        rows: [
            { id: 1, name: '张三', status: '生效中' },
            { id: 2, name: '李四', status: '草稿' },
        ],
    },
} satisfies Meta<typeof BaseTable>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};
