import sys, os, traceback, linecache
from gclog import *

def printExceptionInfo(ex):
    print '----------------------------------'
    traceback.print_exc(file=sys.stderr)
    print '----------------------------------'
    
def getExceptionInfo(ex):
    exc_type, exc_obj, tb = sys.exc_info()
    f = tb.tb_frame
    lineno = tb.tb_lineno
    filename = f.f_code.co_filename
    linecache.checkcache(filename)
    line = linecache.getline(filename, lineno, f.f_globals)
    cd = os.path.dirname(os.path.realpath(__file__+ '/../..'))
    localpartstrlen = len(cd)
    filenamelocal = filename[localpartstrlen:]
    combinedInfoString = 'EXCEPTION IN ({}, LINE {} "{}"): {}'.format(filenamelocal, lineno, line.strip(), exc_obj)
    return (str(exc_obj), exc_obj.args, filenamelocal, lineno, line.strip() )    



if __name__ == "__main__":
    
    def callFuncWithErrLocal():
        raise Exception('anyerror', 'anyerror')
        
    def callFuncWithErrDeeper():
        return callFuncWithErrLocal()
    
    try:
        callFuncWithErrDeeper()
    except Exception as ex:
        print type(ex)     # the exception instance
        #print ex.args      # arguments stored in .args
        print ex           # __str__ allows args to be printed directly 
        #print sys.exc_info()
        exc_type, exc_obj, tb = sys.exc_info()
        formatted_lines = traceback.format_exc().splitlines()
        #print formatted_lines[0]
        #print formatted_lines[-1]
        #print "*** format_exception:"
        #print repr(traceback.format_exception(exc_type, exc_value, exc_traceback))
        ##traceback.print_exc(file=sys.stdout)
            #exc_type, exc_value, exc_tb = sys.exc_info()
            #traceback.print_exception(exc_type, exc_value, exc_tb)
        #print repr(traceback.extract_stack())
        exc_type, exc_value, exc_tb = sys.exc_info()
        from pprint import pprint
        #pprint(traceback.format_exception(exc_type, exc_value, exc_tb))
        stack = traceback.extract_stack()
        pprint(stack)
    
        f = tb.tb_frame
        lineno = tb.tb_lineno
        filename = f.f_code.co_filename
        linecache.checkcache(filename)
        line = linecache.getline(filename, lineno, f.f_globals)
        print 'EXCEPTION IN ({}, LINE {} "{}"): {}'.format(filename, lineno, line.strip(), exc_obj)
    
    #exc_type, exc_obj, exc_tb = sys.exc_info()
    #fname = os.path.split(exc_tb.tb_frame.f_code.co_filename)[1]
    #print(exc_type, fname, exc_tb.tb_lineno)