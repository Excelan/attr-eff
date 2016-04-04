package process.DMS.Regulation.Study;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.ManagedProcessesCentral;
import digital.erp.process.StageOut;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

/**
 * Цель - начало процесса аттестации/asr. Почему не call process? - чтобы передать в asr <- ta, sop
 */
public class CallAOut implements StageOut {

    public void process(ManagedProcessExecution mpe) {

        // Start Attestation

        URN actor = mpe.getInitiator(); // pass initiator

        ManagedProcessExecution returntopme = null; // return control to

        UPN upn = null; // sop urn
        try
        {

            Map<String, String> metadata = new HashMap<String, String>();
            metadata.put("sop", mpe.getMetadataValueByKey("sop"));
            metadata.put("ta", mpe.getSubject().toString());
            metadata.put("nthsession", "1");
            metadata.put("parent", mpe.getUPN().toString());

            upn = ManagedProcessesCentral.getInstance().startProcessWithMetadata(Prototype.fromString("DMS:Regulation:Attestation"), actor, returntopme, null, mpe.getSubject(), metadata);

            //ManagedProcessExecution mpeChild = ManagedProcessExecution.load(upn);

            mpe.setMetadataKeyValue("child", upn.toString());
            mpe.setMetadataKeyValue("firstAttestation", upn.toString());

            //mpeChild.setMetadataKeyValue("parent", mpe.getUPN().toString());
            //mpeChild.setMetadataKeyValue("initiatorofparent", mpe.getInitiator().toString());

            // передаем в процесс Attestation sop, ta
            //mpeChild.setMetadataKeyValue("ta", mpe.getSubject().toString());
            //mpeChild.setMetadataKeyValue("sop", mpe.getMetadataValueByKey("sop"));

            //mpeChild.setMetadataKeyValue("nthsession", "1");

        } catch (Exception e) {
            e.printStackTrace();
        }

        /*
        try {
            String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\" }";
            String gout = HttpRequest.postGetString(Configuration.host()+"/processrouter/DMSRegulationStudy", json);
            System.out.println("ROUTE OK " + gout);
        } catch (Exception e) {
            System.err.println("ROUTE ERROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
        */
    }

}