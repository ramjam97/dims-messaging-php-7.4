<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{config('app.name')}}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="{{ asset('js/app.js') }}" defer"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

</head>

<body class="antialiased">

    <div class="mx-auto h-[90vh] md:h-screen w-screen grid grid-rows-[auto_0.9fr_auto]">

        <!-- Header -->
        <div class="flex justify-center items-center bg-white p-2">
            <div class="w-20 md:w-32 sm:w-30">
                <img class="w-auto" src="{{ asset('imgs/dsims_23c.png') }}" alt="logo">
            </div>
        </div>

        <!-- Middle child (Min height 300px & Stretches) -->
        <div class="h-full min-h-[200px] relative bg-gray-100">

            <div
                class="relative h-full lg:w-[70%] md:w-[80%] mx-auto bg-white relative flex flex-col px-2 md:px-6 flex flex-col gap-3">

                <div
                    class="flex flex-col sm:flex-row flex-wrap gap-2 sm:justify-between items-center text-sm sm:text-base">

                    <span class="flex flex-wrap gap-2 items-center w-full sm:w-auto">
                        <label for="">File:</label>
                        <select
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="selectedLogFile">
                            <option value="">-Select log file-</option>
                            @foreach($logs as $item)
                                <option value="{{ $item['path'] }}" {{$item['selected'] ? 'selected' : ''  }}>
                                    {{$item['name']}}
                                </option>
                            @endforeach
                        </select>
                        <button id="btnRawFile"
                            class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Open Raw
                        </button>
                    </span>

                    <span class="flex flex-wrap gap-4 items-center justify-between w-full sm:w-auto">
                        <span class="flex items-center space-x-2 text-gray-700">
                            <label for="autSyncCB" class="cursor-pointer">Auto-Sync</label>
                            <input type="checkbox" id="autSyncCB"
                                class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring focus:ring-blue-300"
                                checked>
                        </span>
                    </span>

                </div>

                <pre id="output"
                    class="h-full w-full overflow-auto border border-gray-300 p-2 bg-gray-50 rounded-md whitespace-pre-wrap break-words text-xs md:text-sm lg:text-base"></pre>

            </div>

        </div>

        <!-- Footer -->
        <div class="flex flex-row justify-center items-center p-3">
            <div
                class="text-center text-gray-500 dark:text-gray-400 sm:text-right text-xs md:text-sm">
                {{ config('app.name') }} v1.0.1 (PHP v{{ PHP_VERSION }})
            </div>
        </div>

    </div>

</body>

<script>

    const selectLog = document.getElementById('selectedLogFile');
    const buttonRawFile = document.getElementById('btnRawFile');
    const cbAutoSync = document.getElementById('autSyncCB');
    const outputLogs = document.getElementById('output');

    let lastSize = 0; // Track file size to read only new data

    selectLog.addEventListener('change', () => {
        lastSize = 0;
        outputLogs.textContent = '';
        debounceFetchLogs();
    });

    buttonRawFile.addEventListener('click', () => {
        const logUrl = selectLog.value;
        if (logUrl?.trim() === '') return;
        window.open(logUrl, '_blank');
    });

    cbAutoSync.addEventListener('change', () => {
        if (cbAutoSync.checked) fetchNewLogs();
    });

    let fetchLogsTimeout = null;
    function debounceFetchLogs() {
        clearTimeout(fetchLogsTimeout);
        fetchLogsTimeout = setTimeout(fetchNewLogs, 500);
    }

    let autoFetchTimout = null;
    async function fetchNewLogs() {

        clearTimeout(autoFetchTimout);

        const logUrl = selectLog.value;

        if (logUrl?.trim() === '') {
            outputLogs.textContent = 'Select a log file';
            return;
        }

        try {
            console.log('fetching logs...', lastSize);

            const response = await fetch(logUrl, { cache: "no-store" });
            if (!response.ok) throw new Error("Failed to fetch log file");

            const text = await response.text();
            if (text.length > lastSize) {
                const newData = text.slice(lastSize); // Get new content
                outputLogs.textContent += newData;
                lastSize = text.length; // Update the last read size
                scrollToBottom();
            }
        } catch (error) {
            console.error("Error fetching log file:", error);
        } finally {
            if (cbAutoSync.checked) {
                autoFetchTimout = setTimeout(fetchNewLogs, 5_000);
            }
        }
    }

    const scrollToBottom = () => outputLogs.scrollTop = outputLogs.scrollHeight;
    document.body.onload = () => fetchNewLogs();


</script>

</html>