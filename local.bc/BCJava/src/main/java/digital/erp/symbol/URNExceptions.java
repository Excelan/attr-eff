package digital.erp.symbol;

// http://howtodoinjava.com/2012/11/05/best-practices-for-for-exception-handling/

public class URNExceptions {

    public static class IncorrectFormat extends Exception implements URNException
    {
        private static final long serialVersionUID = 3555714415375055302L;
        public IncorrectFormat() {}
        public IncorrectFormat(String msg) {
            super(msg);
        }
    }

    public static class NotURN extends Exception implements URNException
    {
        private static final long serialVersionUID = 3555714415375055305L;
        public NotURN() {}
        public NotURN(String msg) {
            super(msg);
        }
    }

}
