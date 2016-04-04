package odt_new;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class InstructionInfo {
    public String goals="Цель это инструкции заключается в том что бы....";
    public String realmuse ="Областью применения данной инструкции считается...";
    public String responsib ="Ответственность и полномочия работника склада";
    public String resourse="Машины, которые используются на складе";
    public String procedure="Сначала делается...";

    public static class StructInstructParagraph {
        public String sectiontitle;
        public String sectiontext;
    }
    public final List<StructInstructParagraph> paragraphs = new ArrayList<>();

    public String reports="Тут должен быть отчет";
    public String docforlink="Данный документ ссылается на ....";


}
