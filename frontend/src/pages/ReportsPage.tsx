import { useState } from 'react';
import {
  FileText,
  Download,
  Save,
  Calendar,
  Map,
  BarChart3,
} from 'lucide-react';
import toast from 'react-hot-toast';

type ReportType = 'summary' | 'province' | 'timeline' | 'status' | 'custom';

export default function ReportsPage() {
  const [reportType, setReportType] = useState<ReportType>('summary');
  const [province, setProvince] = useState('');
  const [status, setStatus] = useState('');
  const [months, setMonths] = useState(6);
  const [isGenerating, setIsGenerating] = useState(false);

  const handleGenerateReport = async () => {
    setIsGenerating(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 1500));
      toast.success('Report generated successfully');
    } catch (error: any) {
      toast.error('Failed to generate report');
    } finally {
      setIsGenerating(false);
    }
  };

  const handleSaveReport = () => {
    const name = prompt('Enter report name:');
    if (!name) return;
    toast.success('Report configuration saved');
  };

  const savedReports: any[] = [];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
          Reports
        </h1>
        <p className="text-gray-600 dark:text-gray-400">
          Generate and export project reports
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Report Builder */}
        <div className="lg:col-span-2 space-y-6">
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Generate Report
            </h2>

            {/* Report Type Selection */}
            <div className="mb-6">
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Report Type
              </label>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                {[
                  { type: 'summary', icon: BarChart3, label: 'Summary' },
                  { type: 'province', icon: Map, label: 'Province' },
                  { type: 'timeline', icon: Calendar, label: 'Timeline' },
                  { type: 'status', icon: FileText, label: 'By Status' },
                ].map(({ type, icon: Icon, label }) => (
                  <button
                    key={type}
                    onClick={() => setReportType(type as ReportType)}
                    className={`flex flex-col items-center gap-2 p-4 rounded-lg border-2 transition-colors ${
                      reportType === type
                        ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                    }`}
                  >
                    <Icon
                      size={24}
                      className={reportType === type ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400'}
                    />
                    <span
                      className={`text-sm font-medium ${
                        reportType === type
                          ? 'text-blue-600 dark:text-blue-400'
                          : 'text-gray-700 dark:text-gray-300'
                      }`}
                    >
                      {label}
                    </span>
                  </button>
                ))}
              </div>
            </div>

            {/* Report Parameters */}
            <div className="space-y-4">
              {reportType === 'province' && (
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Province
                  </label>
                  <select
                    value={province}
                    onChange={(e) => setProvince(e.target.value)}
                    className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                  >
                    <option value="">Select Province</option>
                    <option value="Bataan">Bataan</option>
                    <option value="Bulacan">Bulacan</option>
                    <option value="Cavite">Cavite</option>
                    <option value="Laguna">Laguna</option>
                    <option value="Pampanga">Pampanga</option>
                    <option value="Rizal">Rizal</option>
                    <option value="Zambales">Zambales</option>
                  </select>
                </div>
              )}

              {reportType === 'status' && (
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Status
                  </label>
                  <select
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                  >
                    <option value="">All Statuses</option>
                    <option value="planning">Planning</option>
                    <option value="in_progress">In Progress</option>
                    <option value="on_hold">On Hold</option>
                    <option value="done">Done</option>
                    <option value="pending">Pending</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>
              )}

              {reportType === 'timeline' && (
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Time Period (months)
                  </label>
                  <input
                    type="number"
                    min="1"
                    max="24"
                    value={months}
                    onChange={(e) => setMonths(Number(e.target.value))}
                    className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                  />
                </div>
              )}
            </div>

            {/* Actions */}
            <div className="mt-6 flex items-center gap-3">
              <button
                onClick={handleGenerateReport}
                disabled={isGenerating || (reportType === 'province' && !province)}
                className="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Download size={20} />
                {isGenerating ? 'Generating...' : 'Generate PDF Report'}
              </button>
              <button
                onClick={handleSaveReport}
                className="flex items-center gap-2 px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold rounded-lg transition-colors"
              >
                <Save size={20} />
                Save Report Config
              </button>
            </div>
          </div>

          {/* Report Description */}
          <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
            <h3 className="text-lg font-semibold text-blue-900 dark:text-blue-400 mb-3">
              Report Descriptions
            </h3>
            <div className="space-y-3 text-sm text-blue-800 dark:text-blue-300">
              <div>
                <strong className="font-semibold">Summary:</strong> Overall project statistics and overview
              </div>
              <div>
                <strong className="font-semibold">Province:</strong> Detailed report for a specific province
              </div>
              <div>
                <strong className="font-semibold">Timeline:</strong> Project trends over a specified period
              </div>
              <div>
                <strong className="font-semibold">By Status:</strong> Filtered report by project status
              </div>
            </div>
          </div>
        </div>

        {/* Saved Reports */}
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            Saved Reports
          </h2>

          {savedReports.length > 0 ? (
            <div className="space-y-3">
              {savedReports.map((report: any) => (
                <div
                  key={report.id}
                  className="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <div className="flex items-start justify-between">
                    <div className="flex-1 min-w-0">
                      <p className="font-medium text-gray-900 dark:text-white truncate">
                        {report.name}
                      </p>
                      <p className="text-xs text-gray-500 dark:text-gray-400 mt-1 capitalize">
                        {report.report_type.replace('_', ' ')}
                      </p>
                      <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {new Date(report.created_at).toLocaleDateString()}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center text-gray-500 dark:text-gray-400 py-8">
              No saved reports
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
