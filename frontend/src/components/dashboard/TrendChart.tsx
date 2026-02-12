import React from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';

interface TrendData {
  date: string;
  new_projects: number;
  completed_projects: number;
  total: number;
}

interface TrendChartProps {
  data: TrendData[];
}

export default function TrendChart({ data }: TrendChartProps) {
  return (
    <div className="h-72">
      <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Project Trends</h3>
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={data}>
          <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
          <XAxis
            dataKey="date"
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
          <Legend
            wrapperStyle={{ color: '#9ca3af' }}
          />
          <Line
            type="monotone"
            dataKey="new_projects"
            stroke="#3b82f6"
            strokeWidth={2}
            dot={{ fill: '#3b82f6', r: 4 }}
            name="New Projects"
          />
          <Line
            type="monotone"
            dataKey="completed_projects"
            stroke="#10b981"
            strokeWidth={2}
            dot={{ fill: '#10b981', r: 4 }}
            name="Completed"
          />
          <Line
            type="monotone"
            dataKey="total"
            stroke="#f59e0b"
            strokeWidth={2}
            dot={{ fill: '#f59e0b', r: 4 }}
            name="Total"
          />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
}
