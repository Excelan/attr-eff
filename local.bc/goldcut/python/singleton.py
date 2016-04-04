class SingletonNS:

    def __init__(self, decorated):
        self._decorated = decorated
        self._instance = {}

    def Instance(self, ns):
        try:
            return self._instance[ns]
        except KeyError:
            self._instance[ns] = self._decorated(ns)
            return self._instance[ns]

    def __call__(self):
        raise TypeError('Singletons must be accessed through `Instance()`.')

    def __instancecheck__(self, inst):
        return isinstance(inst, self._decorated)


class Singleton:

    def __init__(self, decorated):
        self._decorated = decorated

    def Instance(self):
        try:
            return self._instance
        except AttributeError:
            self._instance = self._decorated()
            return self._instance

    def __call__(self):
        raise TypeError('Singletons must be accessed through `Instance()`.')

    def __instancecheck__(self, inst):
        return isinstance(inst, self._decorated)
                
if __name__ == "__main__":

    @Singleton
    class Foo:
       def __init__(self):
           print 'Foo created'

    @SingletonNS
    class FooNS:
       def __init__(self,ns):
           print 'FooNS created', ns

    #f = Foo() # Error
    f = Foo.Instance()
    g = Foo.Instance()
    print f is g # True        
    y = FooNS.Instance('y')
    yy = FooNS.Instance('yy')
    y1 = FooNS.Instance('y')
    print y1 is yy