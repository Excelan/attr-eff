package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class ValidationInfo {
    public String programm="Это программа по валидации, целью которой является....";


    public static class StructMaterialRow {
        public String businessobject;
        public String numberequipment;
        public String specification;
    }
    public final List<StructMaterialRow> tablerows1 = new ArrayList<>();

    public static class StructParametrRow {
        public String titleparametr;
        public String descriptionmethodic;
    }
    public final List<StructParametrRow> tablerows2 = new ArrayList<>();
}
