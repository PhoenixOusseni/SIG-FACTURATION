param(
    [Parameter(Mandatory=$true)][string]$Port,
    [Parameter(Mandatory=$false)][int]$BaudRate = 9600,
    [Parameter(Mandatory=$true)][string]$FrameHex,
    [Parameter(Mandatory=$false)][int]$TimeoutMs = 1500
)

$sp = $null
try {
    $cleanPort = $Port.Trim().ToUpper()
    if (-not $cleanPort.StartsWith("COM")) {
        Write-Host "ERR:INVALID_PORT"
        exit 1
    }

    $sp = New-Object System.IO.Ports.SerialPort $cleanPort, $BaudRate, "None", 8, "One"
    $sp.ReadTimeout = $TimeoutMs
    $sp.WriteTimeout = $TimeoutMs
    $sp.Open()

    # Convert Hex to Bytes
    $bytes = [byte[]]::new($FrameHex.Length / 2)
    for ($i = 0; $i -lt $FrameHex.Length; $i += 2) {
        $bytes[$i / 2] = [Convert]::ToByte($FrameHex.Substring($i, 2), 16)
    }

    # Discard existing input buffer
    $sp.DiscardInBuffer()

    # Send command frame
    $sp.Write($bytes, 0, $bytes.Length)

    # Read response until ETX (0x03)
    $response = [System.Collections.Generic.List[byte]]::new()
    $startTime = [System.DateTime]::Now
    $foundSoh = $false

    while (([System.DateTime]::Now - $startTime).TotalMilliseconds -lt $TimeoutMs) {
        try {
            $b = $sp.ReadByte()
            if ($b -eq 0x15) { # NAK
                Write-Host "NAK"
                $sp.Close()
                exit 0
            }
            if ($b -eq 0x16) { # SYN (Device is busy printing/processing)
                $startTime = [System.DateTime]::Now
                Start-Sleep -Milliseconds 60
                continue
            }
            if ($b -eq 0x01) { # SOH
                $foundSoh = $true
                $response.Clear()
                $response.Add($b)
                continue
            }
            if ($foundSoh) {
                $response.Add($b)
                if ($b -eq 0x03) { # ETX
                    break
                }
            }
        } catch [System.TimeoutException] {
            break
        }
    }

    $sp.Close()

    if ($response.Count -gt 0) {
        $hexOut = [BitConverter]::ToString($response.ToArray()).Replace("-", "")
        Write-Host "OK:$hexOut"
    } else {
        Write-Host "ERR:TIMEOUT"
    }
} catch {
    if ($sp -ne $null -and $sp.IsOpen) {
        $sp.Close()
    }
    Write-Host "ERR:$($_.Exception.Message)"
}
