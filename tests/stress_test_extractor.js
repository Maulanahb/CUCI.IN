// Stress test harness for extractTransactionCode logic from resources/views/lookup/_content.blade.php

function extractTransactionCode(rawText) {
    if (!rawText) return '';
    let code = String(rawText).trim();

    try {
        if (code.includes('code=')) {
            const dummyBase = 'http://localhost';
            const parsedUrl = new URL(code, dummyBase);
            const queryCode = parsedUrl.searchParams.get('code');
            if (queryCode) {
                return queryCode.trim().toUpperCase();
            }
        }
    } catch (e) {
        // URL parse fallback
    }

    const match = code.match(/[?&]code=([^&#]+)/i);
    if (match && match[1]) {
        return decodeURIComponent(match[1]).trim().toUpperCase();
    }

    return code.toUpperCase();
}

const testCases = [
    { input: 'https://portal-cuciin.web.id/lookup?code=TRX-1234', expected: 'TRX-1234', desc: 'Absolute URL HTTPS domain' },
    { input: 'http://localhost:8000/lookup?code=TRX-1234&source=qr', expected: 'TRX-1234', desc: 'Absolute URL HTTP port with multiple params' },
    { input: 'http://127.0.0.1/lookup?code=trx-1234', expected: 'TRX-1234', desc: 'Absolute URL IP lowercase code' },
    { input: 'https://cuci.in/lookup?source=cam&code=TRX-9988#section', expected: 'TRX-9988', desc: 'Absolute URL with anchor hash' },
    { input: '/lookup?code=TRX-1234', expected: 'TRX-1234', desc: 'Relative path with query' },
    { input: '/lookup?foo=bar&code=trx-abcd', expected: 'TRX-ABCD', desc: 'Relative path with secondary query param' },
    { input: '?code=TRX-1234', expected: 'TRX-1234', desc: 'Query string only with question mark' },
    { input: '?foo=1&code=trx-1234', expected: 'TRX-1234', desc: 'Query string only secondary param' },
    { input: 'code=TRX-1234', expected: 'TRX-1234', desc: 'Query string without leading question mark' },
    { input: 'code=TRX-1234&foo=bar', expected: 'TRX-1234', desc: 'Query string multiple params without question mark' },
    { input: 'TRX-1234', expected: 'TRX-1234', desc: 'Raw code hyphenated uppercase' },
    { input: 'TRX1234', expected: 'TRX1234', desc: 'Raw code alphanumeric uppercase' },
    { input: 'trx-1234', expected: 'TRX-1234', desc: 'Raw code lowercase' },
    { input: '  TRK-20261102-CAM01  ', expected: 'TRK-20261102-CAM01', desc: 'Raw code with surrounding whitespace' },
    { input: 'https://portal-cuciin.web.id/lookup?code=TRK%2020261102', expected: 'TRK 20261102', desc: 'URL encoded code in URL' },
    { input: '', expected: '', desc: 'Empty string' },
    { input: null, expected: '', desc: 'Null value' },
    { input: undefined, expected: '', desc: 'Undefined value' }
];

let failures = 0;
console.log('=== STRESS TEST FOR extractTransactionCode ===\n');

testCases.forEach((tc, idx) => {
    const actual = extractTransactionCode(tc.input);
    const pass = actual === tc.expected;
    if (!pass) failures++;
    console.log(`${pass ? '[PASS]' : '[FAIL]'} #${idx + 1} (${tc.desc})`);
    console.log(`       Input:    ${JSON.stringify(tc.input)}`);
    console.log(`       Actual:   ${JSON.stringify(actual)}`);
    console.log(`       Expected: ${JSON.stringify(tc.expected)}\n`);
});

console.log(`Summary: ${testCases.length - failures}/${testCases.length} tests passed. (${failures} failures)`);
if (failures > 0) {
    process.exit(1);
} else {
    process.exit(0);
}
