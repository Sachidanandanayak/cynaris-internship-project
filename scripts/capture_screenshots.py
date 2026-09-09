import subprocess
import time
import urllib.request
import json
import websocket
import tempfile
import base64
import os

def main():
    tmpdir = tempfile.mkdtemp()
    proc = subprocess.Popen([
        r'C:\Program Files\Google\Chrome\Application\chrome.exe',
        '--headless=new',
        '--remote-debugging-port=9222',
        '--remote-allow-origins=*',
        f'--user-data-dir={tmpdir}',
        r'file:///C:/Users/sachin/Documents/Cynaris-Internship/index.html'
    ])
    
    time.sleep(2)
    try:
        tabs = json.loads(urllib.request.urlopen('http://localhost:9222/json').read().decode())
        page_tab = [t for t in tabs if t.get('type') == 'page'][0]
        ws = websocket.create_connection(page_tab['webSocketDebuggerUrl'])
        
        msg_id = 1
        def send(method, params=None):
            nonlocal msg_id
            if params is None:
                params = {}
            msg_id += 1
            payload = {'id': msg_id, 'method': method, 'params': params}
            ws.send(json.dumps(payload))
            while True:
                res = json.loads(ws.recv())
                if res.get('id') == payload['id']:
                    return res

        os.makedirs('screenshots', exist_ok=True)
        
        # 1. 320px Mobile S Viewport
        send('Emulation.setDeviceMetricsOverride', {'width': 320, 'height': 800, 'deviceScaleFactor': 2, 'mobile': True})
        time.sleep(0.5)
        res = send('Page.captureScreenshot', {'format': 'png'})
        with open('screenshots/320px_mobile.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print('Captured screenshots/320px_mobile.png')

        # 2. 320px Mobile Menu Open
        send('Runtime.evaluate', {'expression': 'document.querySelector(".nav-toggle").click()'})
        time.sleep(0.5)
        res = send('Page.captureScreenshot', {'format': 'png'})
        with open('screenshots/320px_mobile_drawer_open.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print('Captured screenshots/320px_mobile_drawer_open.png')

        # Close mobile menu
        send('Runtime.evaluate', {'expression': 'document.querySelector(".nav-toggle").click()'})
        time.sleep(0.3)

        # 3. 768px Tablet Viewport
        send('Emulation.setDeviceMetricsOverride', {'width': 768, 'height': 1024, 'deviceScaleFactor': 2, 'mobile': False})
        time.sleep(0.5)
        res = send('Page.captureScreenshot', {'format': 'png'})
        with open('screenshots/768px_tablet.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print('Captured screenshots/768px_tablet.png')

        # 4. 1024px Desktop Viewport (Hero)
        send('Emulation.setDeviceMetricsOverride', {'width': 1024, 'height': 768, 'deviceScaleFactor': 2, 'mobile': False})
        time.sleep(0.5)
        res = send('Page.captureScreenshot', {'format': 'png'})
        with open('screenshots/1024px_desktop.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print('Captured screenshots/1024px_desktop.png')

        # 5. 1024px Features Grid (3 Columns)
        send('Runtime.evaluate', {'expression': 'document.querySelector("#features").scrollIntoView()'})
        time.sleep(0.5)
        res = send('Page.captureScreenshot', {'format': 'png'})
        with open('screenshots/features_grid_3col.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print('Captured screenshots/features_grid_3col.png')

        # 6. 1024px Testimonials Grid (3 Columns)
        send('Runtime.evaluate', {'expression': 'document.querySelector("#testimonials").scrollIntoView()'})
        time.sleep(0.5)
        res = send('Page.captureScreenshot', {'format': 'png'})
        with open('screenshots/testimonials_grid_3col.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print('Captured screenshots/testimonials_grid_3col.png')

        # 7. 1024px Footer (Semantic 4-column / newsletter flex)
        send('Runtime.evaluate', {'expression': 'document.querySelector("footer").scrollIntoView()'})
        time.sleep(0.5)
        res = send('Page.captureScreenshot', {'format': 'png'})
        with open('screenshots/footer_responsive.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print('Captured screenshots/footer_responsive.png')

        # 8. 1440px Ultrawide Viewport
        send('Runtime.evaluate', {'expression': 'window.scrollTo(0, 0)'})
        send('Emulation.setDeviceMetricsOverride', {'width': 1440, 'height': 900, 'deviceScaleFactor': 2, 'mobile': False})
        time.sleep(0.5)
        res = send('Page.captureScreenshot', {'format': 'png'})
        with open('screenshots/1440px_ultrawide.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print('Captured screenshots/1440px_ultrawide.png')

        # 9. Verification of Breakpoints and Zero Overflow
        audit_results = {}
        for width, height in [(320, 800), (768, 1024), (1024, 768), (1440, 900)]:
            send('Emulation.setDeviceMetricsOverride', {'width': width, 'height': height, 'deviceScaleFactor': 1, 'mobile': width <= 768})
            time.sleep(0.3)
            check_js = '''
            (() => {
                const sw = document.documentElement.scrollWidth;
                const cw = document.documentElement.clientWidth;
                return {
                    viewportWidth: window.innerWidth,
                    scrollWidth: sw,
                    clientWidth: cw,
                    hasHorizontalOverflow: sw > cw,
                    featuresCols: window.getComputedStyle(document.querySelector('.features-grid')).gridTemplateColumns.split(' ').length,
                    testimonialsCols: window.getComputedStyle(document.querySelector('.testimonials-grid')).gridTemplateColumns.split(' ').length,
                    navToggleVisible: window.getComputedStyle(document.querySelector('.nav-toggle')).display !== 'none'
                };
            })()
            '''
            eval_res = send('Runtime.evaluate', {'expression': check_js, 'returnByValue': True})
            audit_results[f"{width}px"] = eval_res['result']['result']['value']
        
        print("\n=== DEVTOOLS RESPONSIVE AUDIT RESULTS ===")
        print(json.dumps(audit_results, indent=2))
        
        with open('screenshots/audit_results.json', 'w') as f:
            json.dump(audit_results, f, indent=2)

    finally:
        proc.terminate()

if __name__ == '__main__':
    main()
