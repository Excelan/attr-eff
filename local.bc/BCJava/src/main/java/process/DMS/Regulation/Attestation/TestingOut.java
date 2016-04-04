package process.DMS.Regulation.Attestation;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class TestingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("++++++++++++++++ TestingOut");
    }

}