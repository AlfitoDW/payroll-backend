
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 8px; border: 1px solid #000; }
        .no-border td { border: none; }
    </style>
</head>
<body>

<h2>SLIP GAJI KARYAWAN</h2>

<table class="no-border">
    <tr>
        <td>Nama</td>
        <td>: {{ $payroll->employee->name }}</td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td>: {{ $payroll->employee->position }}</td>
    </tr>
    <tr>
        <td>Bulan</td>
        <td>: {{ $payroll->month }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>: {{ strtoupper($payroll->status) }}</td>
    </tr>
</table>

<br>

<table>
    <tr>
        <th>Keterangan</th>
        <th>Jumlah</th>
    </tr>
    <tr>
        <td>Gaji Pokok</td>
        <td>Rp {{ number_format($payroll->basic_salary) }}</td>
    </tr>
    <tr>
        <td>Tunjangan</td>
        <td>Rp {{ number_format($payroll->allowance) }}</td>
    </tr>
    <tr>
        <td>Potongan</td>
        <td>Rp {{ number_format($payroll->deduction) }}</td>
    </tr>
    <tr>
        <th>Total Diterima</th>
        <th>Rp {{ number_format($payroll->total_salary) }}</th>
    </tr>
</table>

<br><br>
<p>Dicetak pada: {{ now()->format('d-m-Y') }}</p>

</body>
</html>
