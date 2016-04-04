package process.DMS.Regulation.SOP;

import digital.erp.data.Entity;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.ManagedProcessesCentral;
import digital.erp.process.StageOut;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

import java.util.HashMap;
import java.util.Map;


/**
 * Цель - условное начало процесса создания программы обучения Study
 * в Study будет нужна ссылка на SOP
 */
public class RouteOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        boolean needStudy = false;

        try {

            Integer trainingdocumentFieldValue;
            trainingdocumentFieldValue = Entity.directLoadIntegerForKeyIn("trainingdocument", mpe.getSubject());

            if (trainingdocumentFieldValue == 1) needStudy = true;
            if (needStudy == false) return;

            // Start STUDY

            //mpe.setCurrentactor(null); // ?
            //mpe.saveCurrentactor(null); // ?

            // pass initiator
            URN actor = mpe.getInitiator();

            // return control to
            ManagedProcessExecution returntopme = null;

            URN sop = mpe.getSubject();

            Map<String, String> metadata = new HashMap<String, String>();
            metadata.put("sop", sop.toString());

            // была временная подмена subject на sop (старт процесса study с sop в виде subject), теперь sop передается сразу в меьаданные при старте
            UPN upn = ManagedProcessesCentral.getInstance().startProcessWithMetadata(Prototype.fromString("DMS:Regulation:Study"), actor, returntopme, null, sop, metadata);
            ManagedProcessExecution mpeChild = ManagedProcessExecution.load(upn);

            // set child id
            mpe.setMetadataKeyValue("child", upn.toString());
            // set child parent
            mpeChild.setMetadataKeyValue("parent", mpe.getUPN().toString());
            if (mpe.getInitiator() != null) mpeChild.setMetadataKeyValue("initiatorofparent", mpe.getInitiator().toString());

            // !!! поздно! процесс уже начат и прошел create draft
            // сейчас ставим в createdraftin
            // mpeChild.setMetadataKeyValue("sop", mpe.getSubject().toString());


        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
