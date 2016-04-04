package systest;

//import org.junit.Assert;
//import org.junit.Ignore;
//import org.junit.Test;
//import org.junit.runners.MethodSorters;
//import org.junit.FixMethodOrder;
import org.testng.annotations.*;
import org.testng.Assert;


import digital.erp.process.ManagedProcessExecution;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.process.ManagedProcessesCentral;

//@FixMethodOrder(MethodSorters.NAME_ASCENDING)
//@Ignore
public class TestERPManagedProcess extends Assert {

    /**
    static UPN upn;

    @Test
    public void testManagedProcessCentral() throws Exception {
        System.out.println("========= 1 ManagedProcessesCentral.getInstance()");
        ManagedProcessesCentral mpc = ManagedProcessesCentral.getInstance();
        mpc.availableProcessMastercopies.forEach((name, mpp) -> System.out.println(mpp));
    }

    @Test
    public void testManagedProcessExecutionCreate() throws Exception {
        System.out.println("========= 2 ManagedProcessExecution.create(NEW-CLAIM-UPN, 10-userId)");
        UPN upn = new UPN(Prototype.fromString("x-x-claim"));
        this.upn = upn;
        // TODO ManagedProcessExecution.create(upn, 10);
    }

    @Test
    public void testManagedProcessExecutionLoad() throws Exception {
        System.out.println("========= 3 ManagedProcessExecution.load(this.upn)");
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        assertNotNull(this.upn.getId());
        System.out.println(mpe); //
    }

    @Test
    public void testManagedProcessExecutionLoadAndUpdateMetadataKeyValue() throws Exception {
        System.out.println("========= 4 mpe.setMetadataKeyValue, mpe.getMetadataValueByKey");
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        mpe.setMetadataKeyValue("aa", "NEWAJSONVAL");
        mpe.setMetadataKeyValue("Xc", "123");
        System.out.println(mpe); //
        assertEquals(mpe.getMetadataValueByKey("aa"), "NEWAJSONVAL");
    }

    @Test
    public void testManagedProcessExecutionLoadAndUpdateMetadataKeyValueNext() throws Exception {
        System.out.println("========= 5 more metadata load+save ");
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        assertEquals(mpe.getMetadataValueByKey("aa"), "NEWAJSONVAL");
        mpe.setMetadataKeyValue("aa", "NEWAJSONVAL2");
        assertEquals(mpe.getMetadataValueByKey("aa"), "NEWAJSONVAL2");
        mpe.setMetadataKeyValue("Zc", "456");
        assertEquals(mpe.getMetadataValueByKey("Zc"), "456");
        System.out.println(mpe); //
    }

    @Test
    public void testManagedProcessExecution_CompleteStage() throws Exception {
        System.out.println("========= 6 complete stage PENDING");
//        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
//        mpe.completeCurrentStage();
    }

    */


}