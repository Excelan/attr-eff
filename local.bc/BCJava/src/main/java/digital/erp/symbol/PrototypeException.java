package digital.erp.symbol;

public class PrototypeException {

    public static class IncorrectFormat extends Exception
    {
        private static final long serialVersionUID = 3555714415375055100L;
        public IncorrectFormat() {}
        public IncorrectFormat(String msg) {
            super(msg);
        }
    }

}
