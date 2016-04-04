# -*- coding: utf-8 -*-
import web
import json
import requests
import urlparse
from goldcut import *
from gclog import *
from gcexceptions import *

'''
required POST data.json
required data.json to be JSON
'''


urls = (
    '/(.*)', 'gateserver'
)

class gateserver(web.application):
        
    def run(self, port=9090, *middleware):
        GCLog.Instance().info('gateserver', 'started')
        func = self.wsgifunc(*middleware)
        return web.httpserver.runsimple(func, ('0.0.0.0', port))

    def GET(self, name):
        print "GET", name
        return name

    def POST(self, name):
        web.header('Content-Type', 'application/json')
        #print ">>> POST", name
        #GCLog.Instance().debug('gateinit', name)
        d = web.input()

        try:
            m = json.loads(d.json)
            GCLog.Instance().debug(name, m)
        except Exception as err:
            print "ERROR IN POST.JSON"
            reason = str(type(err))+' '+str(err)
            return json.dumps({'error':'ERROR IN POST.JSON', 'reason': reason})

        try:
            gatename = name.replace('/','.')
            c = import_module_class_instantiated("gates."+gatename)
        except Exception as err:
            (exreason, exargs, filename, filelineno, codeline) = getExceptionInfo(err)
            GCLog.Instance().error('gateinit', "%s, %s:%s" % (exreason, filename, filelineno))
            printExceptionInfo(err)
            return json.dumps({'error':'ERROR IN GATE LOAD AND/OR INIT', 'reason': exreason})
        
        # TODO cookies
        #web.cookies().get(cookieName)
        # setcookie(name, value, expires="", domain=None, secure=False): 
        #web.setcookie('age', i.age, 3600)
        
        try:  
            gateResult = c.gate(m)
            GCLog.Instance().info(name, gateResult)
            return json.dumps(gateResult)
        except Exception as err:
            (exreason, exargs, filename, filelineno, codeline) = getExceptionInfo(err)
            GCLog.Instance().error('gatecall', "%s, %s:%s" % (exreason, filename, filelineno))
            printExceptionInfo(err)
            return json.dumps({'error':'EXCEPTION IN GATE', 'reason': exreason})
        
        

if __name__ == "__main__":
    app = gateserver(urls, globals())
    app.run(port=9090)


