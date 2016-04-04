package digital.erp.domains.document;

import digital.erp.symbol.Prototype;

public class DocumentCodeFactory {

    protected static String codeForPrototype(Prototype prototype, Long id)
    {
        String code = null;
        switch (prototype.getOfClass()) {
            case "Complaint":
                switch (prototype.getOfType()) {
                    case "C_IS":
                        code = "C_IS";
                        break;
                    case "C_IV":
                        code = "C_IV";
                        break;
                    case "C_IW":
                        code = "C_IW";
                        break;
                    case "C_LC":
                        code = "C_LC";
                        break;
                    case "C_LP":
                        code = "C_LP";
                        break;
                    case "C_LT":
                        code = "C_LT";
                        break;
                    case "C_LB":
                        code = "C_LB";
                        break;
                }
                break;
            case "Protocol":
                switch (prototype.getOfType()) {
                    case "II":
                        code = "II";
                        break;
                    case "CT":
                        code = "CT";
                        break;
                    case "EA":
                        code = "EA";
                        break;
                    case "EC":
                        code = "EC";
                        break;
                    case "KI":
                        code = "KI";
                        break;
                    case "MT":
                        code = "MT";
                        break;
                    case "SI":
                        code = "SI";
                        break;
                    case "TM":
                        code = "TM";
                        break;
                    case "VT":
                        code = "VT";
                        break;
                }
                break;
            case "Claim":
                switch (prototype.getOfType()) {
                    case "R_LSC":
                        code = "R_LSC";
                        break;
                    case "R_LSD":
                        code = "R_LSD";
                        break;
                    case "R_LSM":
                        code = "R_LSM";
                        break;
                    case "R_LST":
                        code = "R_LST";
                        break;
                    case "R_OQF":
                        code = "R_OQF";
                        break;
                    case "R_OQR":
                        code = "R_OQR";
                        break;
                    case "R_PAD":
                        code = "R_PAD";
                        break;
                    case "R_PAI":
                        code = "R_PAI";
                        break;
                    case "R_PAT":
                        code = "R_PAT";
                        break;
                    case "R_QDA":
                        code = "R_QDA";
                        break;
                    case "R_QDC":
                        code = "R_QDC";
                        break;
                    case "R_QDE":
                        code = "R_QDE";
                        break;
                    case "R_QDM":
                        code = "R_QDM";
                        break;
                    case "R_RDC":
                        code = "R_RDC";
                        break;
                    case "R_RDD":
                        code = "R_RDD";
                        break;
                    case "R_RDE":
                        code = "R_RDE";
                        break;
                    case "R_TD":
                        code = "R_TD";
                        break;
                    case "R_UPC":
                        code = "R_UPC";
                        break;
                    case "R_UPE":
                        code = "R_UPE";
                        break;
                    case "R_UPI":
                        code = "R_UPI";
                        break;
                    case "R_UPK":
                        code = "R_UPK";
                        break;
                    case "R_UPL":
                        code = "R_UPL";
                        break;
                    case "R_UPP":
                        code = "R_UPP";
                        break;
                }
                break;
            case "Contract":
                switch (prototype.getOfType()) {
                    case "BW":
                        code = "BW";
                        break;
                    case "LC":
                        code = "LC";
                        break;
                    case "LOP":
                        code = "LOP";
                        break;
                    case "LWP":
                        code = "LWP";
                        break;
                    case "MT":
                        code = "MT";
                        break;
                    case "RSS":
                        code = "RSS";
                        break;
                    case "SS":
                        code = "SS";
                        break;
                    case "TMC":
                        code = "TMC";
                        break;
                    case "TME":
                        code = "TME";
                        break;

                }
                break;
            case "Regulations":
                switch (prototype.getOfType()) {
                    case "I":
                        code = "I";
                        break;
                    case "JD":
                        code = "JD";
                        break;
                    case "MP":
                        code = "MP";
                        break;
                    case "P":
                        code = "P";
                        break;
                    case "PV":
                        code = "PV";
                        break;
                    case "SOP":
                        code = "SOP";
                        break;
                    case "TA":
                        code = "TA";
                        break;
                }
                break;
            case "Staffdoc":
                switch (prototype.getOfType()) {
                    case "OF":
                        code = "OF";
                        break;
                    case "OR":
                        code = "OR";
                        break;
                    case "SU":
                        code = "SU";
                        break;
                }
                break;
            case "Tender":
                switch (prototype.getOfType()) {
                    case "TTJ":
                        code = "TTJ";
                        break;
                    case "TTM":
                        code = "TTM";
                        break;
                }
                break;
            case "Contractextension":
                switch (prototype.getOfType()) {
                    case "1":
                        code = "1";
                        break;
                    case "2":
                        code = "2";
                        break;
                    case "3":
                        code = "3";
                        break;
                }
                break;
            case "Copy":
                switch (prototype.getOfType()) {
                    case "Controled":
                        code = "Controled";
                        break;
                    case "Realnoncontrolcopy":
                        code = "Realnoncontrolcopy";
                        break;
                }
                break;
            case "Capa":
                switch (prototype.getOfType()) {
                    case "Deviation":
                        code = "Deviation";
                        break;
                }
                break;
            case "Correction":
                switch (prototype.getOfType()) {
                    case "Capa":
                        code = "Capa";
                        break;
                }
                break;
            case "Solution":
                switch (prototype.getOfType()) {
                    case "Correction":
                        code = "Correction";
                        break;
                }
                break;
            case "Risk":
                switch (prototype.getOfType()) {
                    case "Approved":
                        code = "Approved";
                        break;
                    case "NotApproved":
                        code = "NotApproved";
                        break;
                }

        }
        return code + "-" + id.toString();
    }

}
