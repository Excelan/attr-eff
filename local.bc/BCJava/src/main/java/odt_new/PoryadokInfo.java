package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class PoryadokInfo {
    public String name="Порядок";
    public String goals="Цель этого порядка....";
    public String realmuse ="Областью применения данного порядка считается...";
    public String responsib ="Ответственность и полномочия работника склада";
    public String resourse="Машины, которые используются на складе";
    public String procedure="Сначала делается...";

    public static class StructPoryadokParagraph {
        public String sectiontitle;
        public String sectiontext;
    }
    public final List<StructPoryadokParagraph> paragraphs = new ArrayList<>();

    public String reports="Тут должен быть отчет";
    public String docforlink="Данный документ ссылается на ....";


}