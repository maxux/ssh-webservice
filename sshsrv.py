from flask import Flask, request, render_template, make_response

class SSHWebAuth:
    def __init__(self):
        self.app = Flask(__name__)
        self.app.url_map.strict_slashes = False

    def routes(self):
        @self.app.get('/<user>')
        def authorize(user):
            resp = make_response(render_template("authorize.sh", user=user))
            resp.headers["Content-Type"] = "text/plain"
            return resp

    def listen(self):
        self.app.run(host="0.0.0.0", port=3000, debug=True, threaded=True)

if __name__ == "webssh":
    print("[+] wsgi: initializing ssh-webservice application")

    root = SSHWebAuth()
    root.routes()
    app = root.app
