<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #0f172a;
        line-height: 1.45;
        margin: 0;
    }

    .report-title {
        font-size: 17px;
        font-weight: bold;
        color: #4f46e5;
        margin: 0 0 4px;
    }

    .report-subtitle {
        font-size: 9px;
        color: #64748b;
        margin: 0 0 14px;
    }

    .metrics {
        width: 100%;
        border-collapse: separate;
        border-spacing: 6px 0;
        margin-bottom: 14px;
    }

    .metric-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 10px 8px;
        text-align: center;
    }

    .metric-label {
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }

    .metric-value {
        font-size: 13px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 4px;
    }

    .section-title {
        font-size: 10px;
        font-weight: bold;
        color: #4f46e5;
        margin: 12px 0 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }

    .data-table th {
        background: #4f46e5;
        color: #fff;
        padding: 6px 8px;
        font-size: 8px;
        text-transform: uppercase;
        text-align: left;
    }

    .data-table td {
        padding: 5px 8px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 9px;
        vertical-align: top;
    }

    .data-table tr:nth-child(even) td {
        background: #f8fafc;
    }

    .data-table tfoot td,
    .data-table tr.total-row td {
        font-weight: bold;
        background: #eef2ff;
        border-top: 2px solid #4f46e5;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-success { color: #059669; }
    .text-danger { color: #dc2626; }
    .text-muted { color: #64748b; }

    .chart {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    .chart-label {
        width: 30%;
        font-size: 9px;
        padding: 5px 8px 5px 0;
        vertical-align: middle;
    }

    .chart-bar-cell {
        width: 48%;
        padding: 5px 0;
        vertical-align: middle;
    }

    .chart-bar-track {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .chart-bar-fill {
        height: 12px;
        background: #4f46e5;
    }

    .chart-value {
        width: 22%;
        text-align: right;
        font-size: 9px;
        font-weight: bold;
        padding-left: 8px;
        vertical-align: middle;
    }

    .alert-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        padding: 8px 10px;
        margin-bottom: 12px;
        font-size: 9px;
        color: #991b1b;
    }

    .alert-box.ok {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #065f46;
    }

    .panel {
        border: 1px solid #e2e8f0;
        padding: 12px;
        margin-bottom: 12px;
        background: #fff;
    }

    .panel-title {
        font-size: 10px;
        font-weight: bold;
        color: #334155;
        margin: 0 0 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e2e8f0;
    }

    .pnl-row td {
        padding: 6px 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 10px;
    }

    .pnl-row.subtotal td {
        border-top: 1px solid #94a3b8;
        border-bottom: none;
        font-weight: bold;
        padding-top: 10px;
    }

    .pnl-row.total td {
        border-top: 2px solid #4f46e5;
        border-bottom: none;
        font-size: 12px;
        font-weight: bold;
        padding-top: 12px;
    }

    .report-footer {
        margin-top: 18px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        font-size: 8px;
        color: #94a3b8;
        text-align: center;
    }
</style>
