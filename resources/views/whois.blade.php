<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('home.title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="text-center fw-bold mb-4">{{ __('home.title') }}</h1>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="mb-3 form-floating">
                        <input type="text" id="domain" class="form-control" placeholder="example.com">
                        <label for="domain">{{ __('home.enter_domain') }}</label>
                    </div>

                    <button id="lookup" class="btn btn-primary w-100">
                        Lookup
                    </button>

                    <div id="error" class="alert alert-danger mt-3 d-none text-center"></div>

                    <div id="loading" class="text-center mt-3 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('home.loading') }}</span>
                        </div>
                    </div>

                    <div id="result" class="mt-4 d-none">
                        <h2 class="text-center h5 fw-bold">{{ __('home.whois_info') }}</h2>
                        <pre id="whois-data" class="border rounded p-3 bg-light text-break"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const HIDDEN_CLASS = 'd-none';
    const API_LOOKUP_ROUTE = '{{ route('api.lookup') }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const ENTER_DOMAIN_ERROR = `{{ __('home.enter_domain_error') }}`;
    const FETCH_ERROR = `{{ __('home.fetch_error') }}`;

    const domainInput = document.getElementById('domain');
    const errorAlert = document.getElementById('error');
    const resultSection = document.getElementById('result');
    const loadingIndicator = document.getElementById('loading');
    const whoisDataOutput = document.getElementById('whois-data');
    const lookupButton = document.getElementById('lookup');

    function toggleVisibility(element, shouldShow) {
        element.classList.toggle(HIDDEN_CLASS, !shouldShow);
    }

    function updateTextContent(element, text) {
        element.textContent = text || '';
    }

    async function handleDomainLookup() {
        const domain = domainInput.value.trim();
        if (!domain) {
            showErrorAlert(ENTER_DOMAIN_ERROR);
            toggleVisibility(resultSection, false);
            return;
        }
        try {
            clearErrorAlert();
            toggleVisibility(resultSection, false);
            toggleVisibility(loadingIndicator, true);

            const response = await fetch(API_LOOKUP_ROUTE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ domain })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || FETCH_ERROR);
            }
            renderWhoisData(data.parsed);
            toggleVisibility(resultSection, true);
        } catch (error) {
            showErrorAlert(error.message);
        } finally {
            toggleVisibility(loadingIndicator, false);
        }
    }

    function renderWhoisData(whoisData) {
        whoisDataOutput.innerHTML = '';
        Object.entries(whoisData).forEach(([key, value]) => {
            const lineElement = document.createElement('div');
            lineElement.innerHTML = `<strong>${key}:</strong> ${formatValue(value)}`;
            whoisDataOutput.appendChild(lineElement);
        });
    }

    function formatValue(value) {
        if (Array.isArray(value)) {
            return `<ul>${value.map(item => `<li>${item}</li>`).join('')}</ul>`;
        }
        return value;
    }

    function showErrorAlert(message) {
        updateTextContent(errorAlert, message);
        toggleVisibility(errorAlert, true);
    }

    function clearErrorAlert() {
        updateTextContent(errorAlert, '');
        toggleVisibility(errorAlert, false);
    }

    lookupButton.addEventListener('click', handleDomainLookup);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
