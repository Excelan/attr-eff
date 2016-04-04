package process.DMS.Correction.CAPA;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.ManagedProcessesCentral;
import digital.erp.process.StageOut;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;

public class CallCIOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
       /*
        // TODO решение

        try {
            //mpe.setCurrentactor(null); // ?
            //mpe.saveCurrentactor(null); // ?

            // pass initiator
            URN actor = mpe.getInitiator();

            // return control to
            ManagedProcessExecution returntopme = null;

            UPN upn = ManagedProcessesCentral.getInstance().startProcess(Prototype.fromString("DMS:Correction:CAPAInspection"), actor, returntopme, null, mpe.getSubject());
            ManagedProcessExecution mpeChild = ManagedProcessExecution.load(upn);
            if (mpeChild.getSubject() == null) {
                // default subject is parent subject. redefine in first stage IN later
                mpeChild.setSubject(mpe.getSubject());
                mpeChild.saveSubject(mpe.getSubject());
            }
            // set child id
            mpe.setMetadataKeyValue("child", upn.toString());
            // set child parent
            mpeChild.setMetadataKeyValue("parent", mpe.getUPN().toString());
            mpeChild.setMetadataKeyValue("initiatorofparent", mpe.getInitiator().toString());
        } catch (Exception e) {
            e.printStackTrace();
        }
        */
    }

}
