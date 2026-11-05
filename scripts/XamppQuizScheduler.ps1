[CmdletBinding()]
param(
    [switch]$Install,
    [string]$PhpPath = 'C:\xampp\php\php.exe',
    [ValidateRange(1, 65535)]
    [int]$DatabasePort = 3306
)

$ErrorActionPreference = 'Stop'
$projectPath = Split-Path -Parent $PSScriptRoot
$artisanPath = Join-Path $projectPath 'artisan'
$PhpPath = (Resolve-Path -LiteralPath $PhpPath).ProviderPath

if ($Install) {
    $taskName = 'AbyssalMind Quiz Scheduler'
    $account = [Security.Principal.WindowsIdentity]::GetCurrent().Name
    $powershell = Join-Path $env:SystemRoot 'System32\WindowsPowerShell\v1.0\powershell.exe'
    $arguments = '-NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File "{0}" -PhpPath "{1}" -DatabasePort {2}' -f $PSCommandPath, $PhpPath, $DatabasePort
    $action = New-ScheduledTaskAction -Execute $powershell -Argument $arguments -WorkingDirectory $projectPath
    $logonTrigger = New-ScheduledTaskTrigger -AtLogOn -User $account
    $retryTrigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) `
        -RepetitionInterval (New-TimeSpan -Minutes 1)
    $principal = New-ScheduledTaskPrincipal -UserId $account -LogonType Interactive -RunLevel Limited
    $settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew `
        -ExecutionTimeLimit (New-TimeSpan -Minutes 1) -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries

    Register-ScheduledTask -TaskName $taskName -Action $action -Trigger @($logonTrigger, $retryTrigger) -Principal $principal `
        -Settings $settings -Description 'Wait for XAMPP MySQL and run the AbyssalMind Laravel scheduler.' -Force | Out-Null
    Start-ScheduledTask -TaskName $taskName
    return
}

function Test-XamppDatabase {
    $client = [System.Net.Sockets.TcpClient]::new()
    try {
        return $client.ConnectAsync('127.0.0.1', $DatabasePort).Wait(1000) -and $client.Connected
    } catch {
        return $false
    } finally {
        $client.Dispose()
    }
}

if (-not (Test-XamppDatabase)) {
    return
}

$runningWorker = Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" | Where-Object {
    $_.CommandLine -and $_.CommandLine.IndexOf($artisanPath, [StringComparison]::OrdinalIgnoreCase) -ge 0 `
        -and $_.CommandLine -match '\bschedule:work\b'
} | Select-Object -First 1

if (-not $runningWorker) {
    $workerArguments = '"{0}" schedule:work --no-interaction' -f $artisanPath
    Start-Process -FilePath $PhpPath -ArgumentList $workerArguments -WorkingDirectory $projectPath -WindowStyle Hidden
}
