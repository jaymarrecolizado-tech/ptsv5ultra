import React from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';

interface StatusChartProps {
  data: Record<string, number>;
}

const statusColors: Record<string, string> = {
  planning: '#f59e0b',
  in_progress: '#3b82f6',
  on_hold: '#ef4444',
  done: '#10b981',
  pending: '#6b7280',
  cancelled: '#dc2626',
};

export default function StatusChart({ data }: StatusChartProps) {
  const chartData = Object.entries(data).map(([status, count]) => ({
    status: status.replace('_', ' ').toUpperCase(),
    count,
    originalStatus: status,
  }));

  return (
    <div className="h-72">
      <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Projects by Status</h3>
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={chartData}>
          <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
          <XAxis
            dataKey="status"
            tick={{ fill: '#9ca3af' }}
            axisLine={{ stroke: '#e5e7eb' }}
          />
          <YAxis
            tick={{ fill: '#9ca3af' }}
            axisLine={{ stroke: '#e5e7eb' }}
          />
          <Tooltip
            contentStyle={{
              backgroundColor: '#1f2937',
              border: 'none',
              borderRadius: '8px',
              color: '#fff',
            }}
          />
          <Bar dataKey="count" radius={[8, 8, 0, 0]}>
            {chartData.map((entry) => (
              <Cell key={entry.status} fill={statusColors[entry.originalStatus] || '#6b7280'} />
            ))}
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  );
}
