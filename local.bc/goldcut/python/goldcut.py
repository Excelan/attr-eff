# -*- coding: utf-8 -*-
from  ConfigParser import *
from  singleton import *
import os.path
import sys

sys.path.append(os.path.dirname(os.path.realpath(__file__)) + '/../../python')

def import_module(name):
    mod = __import__(name)
    components = name.split('.')
    for comp in components[1:]:
        mod = getattr(mod, comp)
    return mod

def import_module_class_instantiated(name):
    mod = __import__(name)
    components = name.split('.')
    for comp in components[1:]:
        mod = getattr(mod, comp)
    obj = getattr(mod, comp)()
    return obj


@SingletonNS
class Configuration:
    def __init__(self,ns):
        self._config = ConfigParser()
        self._cfile = os.path.dirname(os.path.realpath(__file__)) + '/../../config/'+ns+'.ini'
        self._config.read(self._cfile)
        self.ns = ns

    def sections(self):
        return self._config.sections()
        
    def sectionitems(self, section):
        return self._config.items(section)        
        
    def get(self,section,param):
        try:
            return self._config.get(section,param)
        except Exception as ex:
            print "--- NO ",  self.ns, section,param
            print ex
            return None

    def getint(self,section,param):
        # TODO catch
        return self._config.getint(section,param)

    def getboolean(self,section,param):
        try:
            return self._config.getboolean(section,param)
        except Exception as ex:
            print "--- NO ", self.ns, section,param
            print ex
            return None

    def getfloat(self,section,param):
        # TODO catch
        return self._config.getfloat(section,param)
    
    def getarray(self,section,param):
        # TODO catch
        return [v.strip() for v in self._config.get(section,param).split(',')]

    def setparam(self, sect, index, value):
        # TODO catch if not exists
        cfgfile = open(self._cfile, 'w')
        self._config.set(sect, index, value)
        self._config.write(cfgfile)
        cfgfile.close()
        