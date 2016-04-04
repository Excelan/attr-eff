package digital.erp.process;

public class ManagedProcessException {

    public static class OperateOnDone extends Exception
    {
        private static final long serialVersionUID = 3555714415375055123L;
        public OperateOnDone() {}
        public OperateOnDone(String msg) {
            super(msg);
        }
    }

    public static class StageNotExists extends Exception
    {
        private static final long serialVersionUID = 3555714415375055125L;
        public StageNotExists() {}
        public StageNotExists(String msg) {
            super(msg);
        }
    }

    public static class StageTypeUnknown extends Exception
    {
        private static final long serialVersionUID = 3555714415375055128L;
        public StageTypeUnknown() {}
        public StageTypeUnknown(String msg) {
            super(msg);
        }
    }

}
