package odt_new;


import org.odftoolkit.odfdom.type.Color;
import org.odftoolkit.simple.TextDocument;
import org.odftoolkit.simple.style.Font;
import org.odftoolkit.simple.style.StyleTypeDefinitions;
import org.odftoolkit.simple.table.Cell;
import org.odftoolkit.simple.table.Table;
import org.odftoolkit.simple.text.Footer;
import org.odftoolkit.simple.text.Header;

import java.util.LinkedList;
import java.util.List;

/**
 * Created by Iryna on 04.01.2016.
 */
public class TechTaskGoodsBuilder {
    public static void main(String[] args) {
        try {
            TextDocument document =TextDocument.newTextDocument();

            //landscape orientation of one page
            /*MasterPage master1=MasterPage.getOrCreateMasterPage(document, "Landscape");
            *master1.setPrintOrientation(PrintOrientation.enumValueOf(PrintOrientation.LANDSCAPE));
            */
            HeaderFooterInfo header= new HeaderFooterInfo();
            String url=header.imgurl;
            String docclass=header.documentclass;
            String doctype=header.documenttype;
            String id=header.iddoc;
            String docversion=header.docversion;
            String docname=header.docname;
            String numbofpage=header.numbofpage;

            String createdoc=header.createdoc;
            String approvedoc=header.approvedoc;
            List<String> discuss=header.discuss;
            discuss=new LinkedList<String>();
            discuss.add("Lavrov");
            discuss.add("Lartsev");
            String myString=discuss.toString();


            TechTaskGoodsInfo goodsInfo =new TechTaskGoodsInfo();
            String docymentname =goodsInfo.docname;
            String workstype=goodsInfo.workstype;
            String group=goodsInfo.group;
            String brunch=goodsInfo.brunch;
            String workdescription=goodsInfo.workdescription;
            String datebegin=goodsInfo.datebegin;
            String datebeend=goodsInfo.datebeend;
            String docspermitted=goodsInfo.docspermitted;
            String audit=goodsInfo.audit;
            String personrecieve=goodsInfo.personrecieve;
            String changes =goodsInfo.changes;
            String сonperson=goodsInfo.conperson;
            String projectdocname=goodsInfo.projectdocname;
            String deliveryconditions=goodsInfo.deliveryconditions;
            String prioritys=goodsInfo.prioritys;

            LinkedList<String> requirement = new LinkedList<String>();
            requirement.add("Требование 1");
            requirement.add("Требование 2");
            requirement.add("Требование 3");

            String attachment=goodsInfo.attachment;

            LinkedList<String> counterparty = new LinkedList<String>();
            counterparty.add("AAA");
            counterparty.add("BBB");
            counterparty.add("CCC");

            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 10, Color.GRAY);

            Header docHeader = document.getHeader();
            Table table = docHeader.addTable(4, 4);

           // Cell cellimage = table.getCellByPosition(0, 0);
           // cellimage.setImage(java.net.URI.create("D:/Quick_odt/quick_odt/images/odf.png"));

          //  table.getCellByPosition(0, 0).setImage(java.net.URI.create(url));
            table.getCellByPosition(1, 0).setStringValue("Класс документа");
            table.getCellByPosition(1, 2).setStringValue("Тип документа");
            table.getCellByPosition(2, 0).setStringValue("Id");
            table.getCellByPosition(2, 2).setStringValue("Версия документа");
            table.getCellByPosition(3, 0).setStringValue("Название документа");
            table.getCellByPosition(3, 2).setStringValue("Страница документа");

           // table.getCellByPosition(0, 4).setStringValue("");

            table.getCellByPosition(0, 0).setFont(myFont);
            table.getCellByPosition(0, 0).setFont(myFont);
            table.getCellByPosition(1, 0).setFont(myFont);
            table.getCellByPosition(1, 2).setFont(myFont);
            table.getCellByPosition(2, 0).setFont(myFont);
            table.getCellByPosition(2, 2).setFont(myFont);
            table.getCellByPosition(3, 0).setFont(myFont);
            table.getCellByPosition(3, 2).setFont(myFont);

            table.getCellByPosition(1, 1).setStringValue(docclass);
            table.getCellByPosition(1, 3).setStringValue(doctype);
            table.getCellByPosition(2, 1).setStringValue(id);
            table.getCellByPosition(2, 3).setStringValue(docversion);
            table.getCellByPosition(3, 1).setStringValue(docname);
            table.getCellByPosition(3, 3).setStringValue(String.valueOf(numbofpage));

            Table table1 = document.addTable(4, 2);
            //table1.applyStyle(template);
            Cell cell = table1.getCellByPosition(0, 0);
            cell.setStringValue("1.Название документа");
            Cell cell2 = table1.getCellByPosition(0, 1);
            cell2.setStringValue("2.Компания группы");
            Cell cell3 = table1.getCellByPosition(0, 2);
            cell3.setStringValue("3.Филиал/подразделение");
            Cell cell4 = table1.getCellByPosition(0, 3);
            cell4.setStringValue("4.Вид работ");

            Cell cell11 = table1.getCellByPosition(1, 0);
            cell11.setStringValue(docymentname);
            Cell cell12 = table1.getCellByPosition(1, 1);
            cell12.setStringValue(group);
            Cell cell13 = table1.getCellByPosition(1, 2);
            cell13.setStringValue(brunch);
            Cell cell14 = table1.getCellByPosition(1, 3);
            cell14.setStringValue(workstype);

            Table table2 = document.addTable(1, 1);
            Cell title= table2.getCellByPosition(0, 0);
            title.setStringValue(workdescription);

            Table table3= document.addTable(1,4);
            Cell cell31 = table3.getCellByPosition(0, 0);
            cell31.setStringValue("6.Срок начала работ, согласно проектного плана");
            Cell cell32 = table3.getCellByPosition(1, 0);
            cell32.setStringValue(datebegin);
            Cell cell33 = table3.getCellByPosition(2, 0);
            cell33.setStringValue("7.Срок окончания работ, согласно проектного плана");
            Cell cell34 = table3.getCellByPosition(3, 0);
            cell34.setStringValue(datebeend);

            Table table4 = document.addTable(2, 1);
            Cell cell41= table4.getCellByPosition(0, 0);
            cell41.setStringValue("8.Требуемая разрешительная документация");
            Cell cell42= table4.getCellByPosition(0, 1);
            cell42.setStringValue(docspermitted);

            Table table5 = document.addTable(4, 2);
            Cell cell51= table5.getCellByPosition(0, 0);
            cell51.setStringValue("9.Необходимость в проведении предварительного аудита");
            Cell cell52= table5.getCellByPosition(0, 1);
            cell52.setStringValue("10.Лицо, ответственное за осуществление технадзора со стороны заказчика");
            Cell cell53= table5.getCellByPosition(0, 2);
            cell53.setStringValue("11.Контактное лицо для ответа на технические вопросы");
            Cell cell54= table5.getCellByPosition(0, 3);
            cell54.setStringValue("12.Необходимость в разработке/ внесении изменений в проектную документацию");

            Cell cell55= table5.getCellByPosition(1, 0);
            cell55.setStringValue(audit);
            Cell cell56= table5.getCellByPosition(1, 1);
            cell56.setStringValue(personrecieve);
            Cell cell57= table5.getCellByPosition(1, 2);
            cell57.setStringValue(сonperson);
            Cell cell58= table5.getCellByPosition(1, 3);
            cell58.setStringValue(changes);

            int num= counterparty.size();
            int reqnum=requirement.size();
            Table table6 = document.addTable(10 + num + reqnum, 1);
            Cell cell61= table6.getCellByPosition(0, 0);
            cell61.setStringValue("13.Название проектной документации достаточной для проведения работ");
            Cell cell62= table6.getCellByPosition(0, 1);
            cell62.setStringValue(projectdocname);
            Cell cell63= table6.getCellByPosition(0, 2);
            cell63.setStringValue("14.Условия поставки");
            Cell cell64= table6.getCellByPosition(0, 3);
            cell64.setStringValue(deliveryconditions);
            Cell cell65= table6.getCellByPosition(0, 4);
            cell65.setStringValue("15.Желаемые приоритеты по порядку выполнения работ");
            Cell cell66= table6.getCellByPosition(0, 5);
            cell66.setStringValue(prioritys);
            Cell cell67= table6.getCellByPosition(0, 6);
            cell67.setStringValue("16.Требования к формированию коммерческого предложения");
            for(int i=0;i<requirement.size();i++) {
                Cell cell68 = table6.getCellByPosition(0, i + 7);
                cell68.setStringValue(requirement.get(i));
            }
            Cell cell69= table6.getCellByPosition(0, 7+reqnum);
            cell69.setStringValue("17.Приложения");
            Cell cell70= table6.getCellByPosition(0, 8+reqnum);
            cell70.setStringValue(attachment);
            Cell cell71= table6.getCellByPosition(0, 9+reqnum);
            cell71.setStringValue("18.Участники тендера");
            for(int i=0;i<counterparty.size();i++) {
                Cell cell72 = table6.getCellByPosition(0, i + 10+reqnum);
                cell72.setStringValue(counterparty.get(i));
            }


            Footer footer = document.getFooter();
            table = footer.addTable(4, 2);

            Cell cellByPosition1 = table.getCellByPosition(0,0);
            cellByPosition1.setStringValue("Разработал:");
            Cell cellByPosition2 = table.getCellByPosition(1,0);
            cellByPosition2.setStringValue("Согласовал(и):");
            Cell cellByPosition3 = table.getCellByPosition(0, 2);
            cellByPosition3.setStringValue("Утвердил:");
            Cell cellByPosition4 = table.getCellByPosition(1, 2);
            cellByPosition4.setStringValue("");

            cellByPosition1.setFont(myFont);
            cellByPosition2.setFont(myFont);
            cellByPosition3.setFont(myFont);
            cellByPosition4.setFont(myFont);

            Cell cellByPosition5 = table.getCellByPosition(0, 1);
            cellByPosition5.setStringValue(createdoc);
            Cell cellByPosition6 = table.getCellByPosition(1,1);
            cellByPosition6.setStringValue(myString);
            Cell cellByPosition7 = table.getCellByPosition(0, 3);
            cellByPosition7.setStringValue(approvedoc);
            Cell cellByPosition8 = table.getCellByPosition(1, 3);
            cellByPosition8.setStringValue("");

            document.save("/home/anton/printForms/techzadangoods.odt");

    } catch (Exception e) {
        e.printStackTrace();
    }
}
}


