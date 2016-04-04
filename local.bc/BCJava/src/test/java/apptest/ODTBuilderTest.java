package apptest;

import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.ManagedProcessesCentral;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.*;
import odt_new.ContractInfoBW;
import org.jsoup.select.Elements;
import org.odftoolkit.odfdom.type.Color;
import org.odftoolkit.simple.TextDocument;
import org.odftoolkit.simple.draw.FrameRectangle;
import org.odftoolkit.simple.draw.FrameStyleHandler;
import org.odftoolkit.simple.draw.Image;
import org.odftoolkit.simple.style.Border;
import org.odftoolkit.simple.style.Font;
import org.odftoolkit.simple.style.StyleTypeDefinitions;
import org.odftoolkit.simple.table.Cell;
import org.odftoolkit.simple.table.Table;
import org.odftoolkit.simple.text.Paragraph;
import org.testng.Assert;
import org.testng.annotations.Test;

import org.jsoup.Jsoup;
//import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;

import javax.imageio.ImageIO;
import javax.json.*;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.StringReader;
import java.lang.reflect.Field;
import java.util.*;

public class ODTBuilderTest {


    @Test(enabled = false)
    public void allPendOnVisa() throws Exception {
        ManagedProcessesCentral.getInstance();

        // удаленный json объект
        JsonObject j = HttpRequest.postGetJsonObject(Configuration.host()+"/universalload/Contract/Decision/Contract/BW", "{\"urn\":\"urn:Document:Contract:BW:843815287\"}");

        // целевой объект
        ContractInfoBW info = new ContractInfoBW();

        JsonRemap.remap(info, j);

        // top level remap (simple+units)
        // {} remap
        // [] remap
        // [] -> [] remap

        JsonArray jsonArray = j.getJsonArray("contractapplication");
        for (int n = 0; n < jsonArray.size(); n++) {
            JsonObject object = jsonArray.getJsonObject(n);
            //System.out.println(object);
            ContractInfoBW.ApplicationRow row1 = new ContractInfoBW.ApplicationRow();
            JsonRemap.remap(row1, object);
            JsonArray jsonArrayIn = object.getJsonArray("MediaAttributed");
            for (int maidx = 0; maidx < jsonArrayIn.size(); maidx++) {
                JsonObject objectin = jsonArrayIn.getJsonObject(maidx);
                //System.out.println("\t" + objectin);
                ContractInfoBW.ApplicationRow.MediaRow subrow1 = new ContractInfoBW.ApplicationRow.MediaRow();
                row1.mediarows.add(subrow1);
                JsonRemap.remap(subrow1, objectin);
                System.out.println("---- "+ objectin);
                System.out.println("? "+ subrow1.attachment);
            }
            info.tablerows.add(row1);
        }

        System.out.println("!!!!! PLACE " + info.place);
        System.out.println("!!!!! PLACE " + info.timecontract);

        TextDocument document = TextDocument.newTextDocument();

        String number = info.number;
        String place = info.place;
        String contdate = info.contdate;
        String introduction = info.introduction;
        String contractsubject = info.contractsubject;

        String rightsandliabilities = info.rightsandliabilities;
        String timeofworks = info.timeofworks;
        String termofcustompayments = info.termofcustompayments;
        String payments = info.payments;
        String specialconditions = info.specialconditions;
        String otherconditions = info.otherconditions;

        String requisites = info.requisites;
        String requisitesco = info.requisitescont;
        String director = info.director;


        Paragraph paragraph1 = document.addParagraph("Договор №" + number);
        paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
        paragraph1.setFont(myFont);
        Paragraph paragraph11 = document.addParagraph("на оказание услуг ТЛС и СТЗ");
        paragraph11.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        Font myFont3 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);
        paragraph11.setFont(myFont3);
        Paragraph paragraph12 = document.addParagraph("");

        Paragraph paragraph214 = document.addParagraph(place);
        paragraph214.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.LEFT);

        Paragraph paragraph215 = document.addParagraph(contdate);
        paragraph215.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);

        Paragraph paragraph2 = document.addParagraph("");
        document.addParagraph("");
        paragraph2.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        Font myFont2 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);
        paragraph2.setFont(myFont2);
        Paragraph paragraph21 = document.addParagraph(introduction);
        Paragraph paragraph22 = document.addParagraph("");

        Paragraph paragraph3 = document.addParagraph("1.Предмет договора");
        document.addParagraph("");
        paragraph3.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        paragraph3.setFont(myFont2);
        Paragraph paragraph31 = document.addParagraph(contractsubject);
        Paragraph paragraph32 = document.addParagraph("");

        Paragraph paragraph4 = document.addParagraph("2.Права и обязанности сторон");
        document.addParagraph("");
        paragraph4.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        paragraph4.setFont(myFont2);
        Paragraph paragraph41 = document.addParagraph(rightsandliabilities);
        Paragraph paragraph42 = document.addParagraph("");

        Paragraph paragraph5 = document.addParagraph("3.Срок выполнения работ");
        document.addParagraph("");
        paragraph5.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        paragraph5.setFont(myFont2);
        Paragraph paragraph51 = document.addParagraph(timeofworks);
        Paragraph paragraph52 = document.addParagraph("");

        Paragraph paragraph6 = document.addParagraph("4.Условия оплаты таможенных платежей");
        document.addParagraph("");
        paragraph6.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        paragraph6.setFont(myFont2);
        Paragraph paragraph61 = document.addParagraph(termofcustompayments);
        Paragraph paragraph62 = document.addParagraph("");

        Paragraph paragraph7 = document.addParagraph("5.Расчеты сторон");
        document.addParagraph("");
        paragraph7.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        paragraph7.setFont(myFont2);
        Paragraph paragraph71 = document.addParagraph(payments);
        Paragraph paragraph72 = document.addParagraph("");

        Paragraph paragraph8 = document.addParagraph("5.Особенные условия и ответственность сторон");
        document.addParagraph("");
        paragraph8.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        paragraph8.setFont(myFont2);
        Paragraph paragraph81 = document.addParagraph(specialconditions);
        Paragraph paragraph82 = document.addParagraph("");

        Paragraph paragraph9 = document.addParagraph("6.Другие условия");
        document.addParagraph("");
        paragraph9.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        paragraph9.setFont(myFont2);
        Paragraph paragraph91 = document.addParagraph(otherconditions);
        Paragraph paragraph92 = document.addParagraph("");


        Paragraph paragraph211 = document.addParagraph("6.Реквизиты сторон");
        document.addParagraph("");
        paragraph211.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        paragraph211.setFont(myFont2);

        Table table1 = document.addTable(4, 2);
        Cell cell = table1.getCellByPosition(0, 0);
        cell.setStringValue("Исполнитель:");
        Cell cell2 = table1.getCellByPosition(1, 0);
        cell2.setStringValue("Заказчик:");
        Cell cell3 = table1.getCellByPosition(0, 1);
        cell3.setStringValue(requisites);
        Cell cell4 = table1.getCellByPosition(1, 1);
        cell4.setStringValue(requisitesco);
        Cell cell5 = table1.getCellByPosition(0, 2);
        cell5.setStringValue("Директор");
        Cell cell6 = table1.getCellByPosition(1, 2);
        cell6.setStringValue("Директор");
        Cell cell7 = table1.getCellByPosition(0, 3);
        cell7.setStringValue("_______________________ " + director);
        Cell cell8 = table1.getCellByPosition(1, 3);
        cell8.setStringValue("_______________________");

        Border borderbase = new Border(Color.WHITE, 2, StyleTypeDefinitions.SupportedLinearMeasure.PT);

        cell.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
        cell.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
        cell2.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
        cell2.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
        cell3.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
        cell3.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
        cell4.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
        cell4.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
        cell5.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
        cell5.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
        cell6.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
        cell6.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
        cell7.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
        cell7.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
        cell8.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
        cell8.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);

        cell.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
        cell2.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);

        cell.setFont(myFont3);
        cell2.setFont(myFont3);

        document.addPageBreak();

        String basedir = FileSystem.basedir();

        for (int ii = 0; ii < info.tablerows.size(); ii++) {
            Paragraph paragraph219 = document.addParagraph("Приложение " + ((Integer) 1 + (Integer) ii) * 1 + " к Договору №" + number + " от " + contdate);
            document.addParagraph("");
            paragraph219.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph219.setFont(myFont2);
            document.addParagraph("");
            document.addParagraph(info.tablerows.get(ii).text);
            Paragraph paragraph1003 = document.addParagraph("");
            Paragraph paragraph1001 = document.addParagraph("");
            Paragraph paragraph1000 = document.addParagraph("");
            document.insertTable(paragraph1000, table1, true);
            document.addPageBreak();
            for (int jj = 0; jj < info.tablerows.get(ii).mediarows.size(); jj++)
            {

                String filename = basedir + info.tablerows.get(ii).mediarows.get(jj).attachment.trim();
                String mediauri = "file://" + filename;
                System.out.println(mediauri);
                System.out.println(info.tablerows.get(ii).mediarows.get(jj).attachment.trim());
                //document.newImage(java.net.URI.create(mediauri));

                BufferedImage bimg = ImageIO.read(new File(filename));
                int width          = bimg.getWidth();
                int height         = bimg.getHeight();
                float aspect = width / height;
                DebugPrinter.formatted("%d %d", width, height);

                Paragraph npara = document.addParagraph("");
                Image image = Image.newImage(npara, new java.net.URI(mediauri)); //
                float imgwidthonpage = 17;
                float imgheightonpage = imgwidthonpage / aspect;
                DebugPrinter.formatted("%f %f", imgwidthonpage, imgheightonpage);
                image.setRectangle(new FrameRectangle(0,0, imgwidthonpage, imgheightonpage, StyleTypeDefinitions.SupportedLinearMeasure.CM));

                FrameStyleHandler handler = image.getStyleHandler();
                handler.setAchorType(StyleTypeDefinitions.AnchorType.AS_CHARACTER);
                handler.setHorizontalRelative(StyleTypeDefinitions.HorizontalRelative.PAGE);
                handler.setVerticalRelative(StyleTypeDefinitions.VerticalRelative.PAGE);

                /*
                Image image = Image.newImage(para, new URI("file:/c:/image.jpg"));
                image.setTitle("Image title");
                image.setDescription("This is a sample image");
                image.setVerticalPosition(FrameVerticalPosition.TOP);
                image.setHorizontalPosition(FrameHorizontalPosition.RIGHT);
                image.setHyperlink(new URI("http://odftoolkit.org"));
                //If you want to handle more style settings of image, you can try FrameStyleHandler.
                FrameStyleHandler handler = image.getStyleHandler();
                handler.setAchorType(AnchorType.AS_CHARACTER);
                handler.setHorizontalRelative(HorizontalRelative.PAGE);
                handler.setVerticalRelative(VerticalRelative.PAGE);
                 */
                //document.addParagraph(info.tablerows.get(ii).mediarows.get(jj).attachment); // TODO img
                document.addParagraph("");
                Paragraph paragraph220 = document.addParagraph(info.tablerows.get(ii).mediarows.get(jj).text);
                paragraph220.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
                Paragraph paragraph1002 = document.addParagraph("");
                document.insertTable(paragraph1002, table1, true);
                document.addPageBreak();
            }
        }
        document.save(basedir + "/tmp/contractBW.odt");



        // Parse HTML String using JSoup library
        String HTMLSTring = "<p>Para1 <b>bold</b> text</p><p>Para2 <b>bold</b> text</p><p>Para3 <b>bold3</b> text</p>";

        org.jsoup.nodes.Document html = Jsoup.parse(HTMLSTring);
        //String h1 = html.body().getElementsByTag("h1").text();

        System.out.println("Input HTML String to JSoup :" + HTMLSTring);
        //System.out.println("Afte parsing, Heading : " + h1);

        Elements links = html.select("p");
        DebugPrinter.formatted("\nLinks: (%d)", links.size());
        for (Element link : links) {
            DebugPrinter.formatted(" * a: <%s>  (%s)", link.attr("abs:href"), link.text());
        }

    }

}
/*
System.out.println(objectin);
Set<Map.Entry<String, JsonValue>> ma = objectin.entrySet();
ma.forEach((kv)-> {
    String k = kv.getKey();
    JsonValue v = kv.getValue();
    System.out.println("MediaAttributed Item : " + k + " V : " + v);
});
*/
