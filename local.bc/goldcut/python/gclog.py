# -*- coding: utf-8 -*-
import logging, os
from singleton import *
from goldcut import Configuration

@Singleton
class GCLog:
    def __init__(self):
        self.enabled = False
        ps = Configuration.Instance('project')
        hs = Configuration.Instance('host')
        ENV = ps.get('ENVIRONMENT','ENV')
        LOG_ENV = ps.get('LOGGING','LOG_ENV')
        if ENV == LOG_ENV: 
            self.enabled = True
            #print "+++ LOGGING ENABLED"
        host = hs.get('PROJECT','HOST')
        
        base = os.path.dirname(os.path.realpath(__file__)) + '/../../log'
        logfile = base+'/_PYTHON.log'
        logging.basicConfig(level=logging.DEBUG, format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s',datefmt='%m-%d %H:%M',filename=logfile,filemode='a')
        if hs.getboolean('LOGGING','CONSOLELOGPY'): 
            self.enableConsoleOutput(logging.DEBUG) # TODO from config
            #print "+++ CONSOLE LOGGING ENABLED"

    def enableConsoleOutput(self, level):
        console = logging.StreamHandler() # define a Handler which writes INFO messages or higher to the sys.stderr
        console.setLevel(level)
        formatter = logging.Formatter('%(name)-12s: %(levelname)-8s %(message)s') # set a format which is simpler for console use
        console.setFormatter(formatter) 
        logging.getLogger('').addHandler(console) # add the handler to the root logger    
        
    def getLogger(self, ns):
        return logging.getLogger(ns)
    
    def debug(self, ns, message):
        log = logging.getLogger(ns)
        log.debug(message)
    
    def info(self, ns, message):
        log = logging.getLogger(ns)
        log.info(message)
        
    def error(self, ns, message):
        log = logging.getLogger(ns)
        log.error(message)        