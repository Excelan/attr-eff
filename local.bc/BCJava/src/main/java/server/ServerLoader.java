package server;

import experiments.server.HttpServer;
import org.odftoolkit.odfdom.doc.*;
import org.odftoolkit.odfdom.type.Color;
import org.odftoolkit.simple.*;
import org.odftoolkit.simple.common.navigation.TextNavigation;
import org.odftoolkit.simple.common.navigation.TextSelection;
import org.odftoolkit.simple.table.*;
import org.odftoolkit.simple.text.Paragraph;
import org.odftoolkit.simple.text.list.List;
import org.odftoolkit.simple.text.list.ListDecorator;
import org.odftoolkit.simple.text.list.ListItem;
import org.odftoolkit.simple.text.list.NumberDecorator;

public class ServerLoader {

    public static void main(String[] args) throws Exception {



        // https://github.com/apache/odftoolkit

        // http://www.langintro.com/odfdom_tutorials/quick_odt.html SIMPLEST
        // ODS http://www.langintro.com/odfdom_tutorials/create_ods.html
        // http://incubator.apache.org/odftoolkit/simple/document/cookbook/Text%20Document.html#List
        // http://incubator.apache.org/odftoolkit/simple/document/cookbook/Table.html
        // http://incubator.apache.org/odftoolkit/simple/document/cookbook/Text%20Document.html#List
        // https://incubator.apache.org/odftoolkit/simple/demo/demo7.html
        // https://incubator.apache.org/odftoolkit/simple/demo/demo3.html
        // https://incubator.apache.org/odftoolkit/simple/demo/demo10.html
        // https://incubator.apache.org/odftoolkit/simple/demo/demo4.html
        // https://incubator.apache.org/odftoolkit/simple/demo/demo6.html

        /*
        https://wiki.openoffice.org/wiki/Writer/Input_Fields
        https://wiki.openoffice.org/w/images/d/d9/0214WG-WorkingWithFields.pdf
        https://forum.openoffice.org/en/forum/viewtopic.php?f=29&t=48684
        http://www.techrepublic.com/blog/linux-and-open-source/three-great-ways-to-use-variables-in-libreoffice-and-openoffice/

         */


        if (false) {

            System.out.println("!!! GENERATE ODT");

            OdfTextDocument odt = OdfTextDocument.newTextDocument();
            // Append text to the end of the document.
            odt.addText("This is my very first ODF test");
            // Save document
            odt.save("_test1.odt");

            TextDocument document = TextDocument.newTextDocument();

            Table table1 = Table.newTable(document);
            table1.setTableName("table1");
            Table table = document.getTableByName("table1");
            Cell cell = table.getCellByPosition(1, 1);
            cell.setCellBackgroundColor(Color.valueOf("#f0ffed"));
            cell.setStringValue("TEST тест");

            ListDecorator numberDecorator = new NumberDecorator(document);
            List list = document.addList(numberDecorator);
            String header = list.getHeader();
            list.setHeader("NewHeader");
            ListItem newItem1 = list.addItem("x REPLACEME x");
            ListItem newItem2 = list.addItem("REPLACEPARA");
            ListItem newItem3 = list.addItem("itemContent 3");
            List listSub = newItem2.addList(numberDecorator);
            ListItem newItem21 = listSub.addItem("itemContent 2.1");
            document.save("_testSimple.odt");

            TextDocument document2 = TextDocument.loadDocument("_testSimple.odt");
            TextNavigation search2 = new TextNavigation("REPLACEME", document2);
            while (search2.hasNext()) {
                TextSelection item = (TextSelection) search2.nextSelection();
                item.replaceWith("Test 1 test test test test test test test test test test test test test test \n\n test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test");
            }

            search2 = new TextNavigation("REPLACEPARA", document2);
            while (search2.hasNext()) {
                TextSelection item = (TextSelection) search2.nextSelection();
                //Paragraph paragraph = document2.getParagraphByIndex(0, true);
                Paragraph paragraph = document2.addParagraph(null);
                paragraph.setTextContent("* Experience certificates from previous employers \n* Copy of resignation/acceptation letter and relieving letter");
                item.replaceWith(paragraph);
            }
            document2.save("_testSimpleChanged.odt");
        }


        HttpServer.run();

    }
}
