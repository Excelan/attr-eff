package process.DMS.Regulation.Attestation;

import digital.erp.data.Entity;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.ManagedProcessesCentral;
import digital.erp.process.StageOut;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;

import java.util.*;
import java.util.stream.Collectors;

public class RouteOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        try {

            System.out.println("=== TODO RouteOut Attestation ===");

            URN asr = mpe.getSubject();

            // 1 - START ATTESTATION FOR FAILED STUDENTS (same N of interval)

            // Nth
            Integer nthsession = Integer.parseInt(mpe.getMetadataValueByKey("nthsession"));
            //if (nthsession == null) nthsession = 1;

            // load planned
            List<URN> planned = Entity.directLoadURNListForKeyIn("plannedattendees", asr);
            System.out.println("Planned count: " + planned.size());

            // TODO testenv = false когда R будет добавлять в ASR successpassed
            boolean testenv = false;
            // mock test -> add random number of passes
            // 1 run: half will pass
            // 2 run: 1 will pass
            // 3 run: all left will pass
            // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! TEST
            if (testenv == true) {
                Long rand;
                if (nthsession == 1)
                    rand = URN.randomInRange(1, (long) (planned.size() / 2)); // сдаст до половины, минимум 1
                else if (nthsession == 2) rand = 1L; // сдаст только 1
                else rand = (long) planned.size(); // сдадут все
                for (int i = 0; i < rand.intValue(); i++) {
                    Entity.directArrayAppendString(asr, "successpassed", planned.get(i).toString());
                    System.out.println("MOCK PASS!");
                }
            }
            // TEST END

            // load passed
            List<URN> passed = Entity.directLoadURNListForKeyIn("successpassed", asr);
            System.out.println("Passed count: " + passed.size());

            List<URN> listNotPassedFromPlanned = new ArrayList(planned); // copy of planned
            listNotPassedFromPlanned.removeAll(passed); // planned minus passed

            // start UKD
            if (passed.size() > 0)
            {
                URN sop = new URN(mpe.getMetadataValueByKey("sop"));
                Integer version = 1; // TODO

                Map<String, String> metadata = new HashMap<>();
                metadata.put("asr", asr.toString());
                metadata.put("sop", sop.toString());
                metadata.put("sopversion", version.toString());

                URN initiator = null;
                ManagedProcessExecution returntopme = null;

                UPN upnUKD = ManagedProcessesCentral.getInstance().startProcessWithMetadata(Prototype.fromString("DMS:Regulation:UKD"), initiator, returntopme, null, null, metadata);
                mpe.setMetadataKeyValue("ukd", upnUKD.toString());
            }

            //Set<String> setPlanned = planned.stream().map(URN::toString).collect(Collectors.toCollection(TreeSet::new));
            //Set<String> setPassed = passed.stream().map(URN::toString).collect(Collectors.toCollection(TreeSet::new));
            //Set<String> setNotPassedFromPlanned
            // setPlanned.removeAll(setPassed);

            // restart Attestation
            if (passed.size() < planned.size())
            {
                listNotPassedFromPlanned.forEach(np -> System.out.println(np));

                mpe.setMetadataKeyValue("allpassed", "no");
                mpe.setMetadataKeyValue("passedcount", String.valueOf(passed.size()));
                mpe.setMetadataKeyValue("plannedcount", String.valueOf(planned.size()));

                // pass initiator
                URN actor = mpe.getInitiator();

                // return control to
                ManagedProcessExecution returntopme = null;

                // metadata
                Map<String, String> metadata = new HashMap<String, String>();
                metadata.put("sop", mpe.getMetadataValueByKey("sop"));
                metadata.put("ta", mpe.getMetadataValueByKey("ta"));
                nthsession++;
                metadata.put("nthsession", nthsession.toString());
                metadata.put("prev", mpe.getUPN().toString());
                //metadata.put("prevxx", mpe.getUPN().toString());
                System.out.println("TA CHECK: ");
                System.out.println(metadata); // .forEach((k, v) -> System.out.println(k + "=" + v));


                // start Attestation again
                UPN upn = ManagedProcessesCentral.getInstance().startProcessWithMetadata(Prototype.fromString("DMS:Regulation:Attestation"), actor, returntopme, null, null, metadata);
                //ManagedProcessExecution mpeChild = ManagedProcessExecution.load(upn);

                mpe.setMetadataKeyValue("next", upn.toString());
                //mpeChild.setMetadataKeyValue("prev", mpe.getUPN().toString());

                //mpeChild.setMetadataKeyValue("", );
            }
            else
            {
                mpe.setMetadataKeyValue("allpassed", "yes");
            }

            // 2 - START UKD FOR SUCCESSFULL STUDENTS (из ASR взять???)

            // TODO


        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
