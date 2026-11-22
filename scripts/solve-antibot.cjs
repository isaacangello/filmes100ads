#!/usr/bin/env node
// solve-antibot.js — Resolve InfinityFree anti-bot challenge and make HTTP requests
const https = require('https');
const http = require('http');
const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const os = require('os');

const TARGET = process.argv[2] || 'https://p4o.free.nf/';
const POST_DATA = process.argv[3] || null; // file path for POST
const HEADERS = {};

for (let i = 4; i < process.argv.length; i++) {
  const [k, v] = process.argv[i].split(':');
  HEADERS[k] = v;
}

function fetch(url, cookies = '') {
  return new Promise((resolve, reject) => {
    const u = new URL(url);
    const opts = {
      hostname: u.hostname,
      port: u.port || 443,
      path: u.pathname + u.search,
      method: 'GET',
      rejectUnauthorized: false,
      headers: {
        'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
        ...(cookies ? { Cookie: cookies } : {})
      }
    };
    https.get(opts, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => resolve({ status: res.statusCode, body: data, headers: res.headers }));
    }).on('error', reject);
  });
}

function postFile(url, filePath, cookies = '') {
  return new Promise((resolve, reject) => {
    const u = new URL(url);
    const boundary = '----P4OBoundary' + Date.now();
    const fileContent = fs.readFileSync(filePath);
    const fileName = path.basename(filePath);

    const before = Buffer.from(
      `--${boundary}\r\nContent-Disposition: form-data; name="sql"; filename="${fileName}"\r\nContent-Type: application/octet-stream\r\n\r\n`
    );
    const after = Buffer.from(`\r\n--${boundary}--\r\n`);
    const body = Buffer.concat([before, fileContent, after]);

    const opts = {
      hostname: u.hostname,
      port: u.port || 443,
      path: u.pathname + u.search,
      method: 'POST',
      rejectUnauthorized: false,
      headers: {
        'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
        'Content-Type': `multipart/form-data; boundary=${boundary}`,
        'Content-Length': body.length,
        ...(HEADERS['X-Deploy-Key'] ? { 'X-Deploy-Key': HEADERS['X-Deploy-Key'] } : {}),
        ...(cookies ? { Cookie: cookies } : {})
      }
    };

    const req = https.request(opts, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => resolve({ status: res.statusCode, body: data, headers: res.headers }));
    });
    req.on('error', reject);
    req.write(body);
    req.end();
  });
}

function toNumbers(hex) {
  const e = [];
  for (let i = 0; i < hex.length; i += 2) e.push(parseInt(hex.substr(i, 2), 16));
  return e;
}

function toHex(arr) {
  let s = '';
  for (let i = 0; i < arr.length; i++) s += (16 > arr[i] ? '0' : '') + arr[i].toString(16);
  return s.toLowerCase();
}

async function solveChallenge(url) {
  const res = await fetch(url);
  const matches = [...res.body.matchAll(/toNumbers\("([0-9a-f]+)"\)/g)];

  if (matches.length < 3) {
    // No challenge or already authenticated
    return { cookies: '', body: res.body, headers: res.headers };
  }

  console.error(`[antibot] Challenge detected for ${url}`);

  // Save aes.js and run via node
  const aesRes = await fetch(new URL('/aes.js', url).href);
  const aesFile = path.join(os.tmpdir(), 'aes.js');
  fs.writeFileSync(aesFile, aesRes.body);

  const a = matches[0][1], b = matches[1][1], c = matches[2][1];

  const script = `
    eval(require('fs').readFileSync('${aesFile}', 'utf8'));
    function toNumbers(d){var e=[];d.replace(/(..)/g,function(d){e.push(parseInt(d,16))});return e}
    function toHex(){for(var d=[],d=1==arguments.length&&arguments[0].constructor==Array?arguments[0]:arguments,e="",f=0;f<d.length;f++)e+=(16>d[f]?"0":"")+d[f].toString(16);return e.toLowerCase()}
    var a=toNumbers("${a}"),b=toNumbers("${b}"),c=toNumbers("${c}");
    process.stdout.write(toHex(slowAES.decrypt(c,2,a,b)));
  `;

  const scriptFile = path.join(os.tmpdir(), 'solve.js');
  fs.writeFileSync(scriptFile, script);
  const cookie = execSync(`node ${scriptFile}`, { encoding: 'utf-8', timeout: 5000 }).trim();
  console.error(`[antibot] Cookie solved: ${cookie.substring(0, 20)}...`);

  // Re-fetch with cookie
  const res2 = await fetch(url, `__test=${cookie}`);

  // Check if another challenge round
  const m2 = [...res2.body.matchAll(/toNumbers\("([0-9a-f]+)"\)/g)];
  if (m2.length >= 3) {
    console.error('[antibot] Second challenge round needed...');
    // Extract Set-Cookie from first response and combine
    const setCookie = res.headers['set-cookie'];
    let cookieStr = `__test=${cookie}`;
    if (setCookie) {
      for (const sc of setCookie) {
        const m = sc.match(/^([^=]+)=([^;]+)/);
        if (m) cookieStr += `; ${m[1]}=${m[2]}`;
      }
    }
    const res3 = await fetch(url, cookieStr);
    return { cookies: cookieStr, body: res3.body, headers: res3.headers };
  }

  // Combine cookies
  let cookieStr = `__test=${cookie}`;
  const setCookie = res2.headers['set-cookie'] || res.headers['set-cookie'];
  if (setCookie) {
    for (const sc of (Array.isArray(setCookie) ? setCookie : [setCookie])) {
      const m = sc.match(/^([^=]+)=([^;]+)/);
      if (m) cookieStr += `; ${m[1]}=${m[2]}`;
    }
  }

  return { cookies: cookieStr, body: res2.body, headers: res2.headers };
}

async function main() {
  console.error(`[p4o] Target: ${TARGET}`);

  const { cookies, body } = await solveChallenge(TARGET);

  if (POST_DATA) {
    console.error(`[p4o] POST with file: ${POST_DATA}`);
    const res = await postFile(TARGET, POST_DATA, cookies);
    console.error(`[p4o] Response status: ${res.status}`);
    console.log(res.body);
  } else {
    console.error(`[p4o] Response length: ${body.length}`);
    console.log(body);
  }
}

main().catch(e => { console.error(`[p4o] Error: ${e.message}`); process.exit(1); });
