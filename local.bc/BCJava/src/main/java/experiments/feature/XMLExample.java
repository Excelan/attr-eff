package experiments.feature;

import java.io.File;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import javax.xml.xpath.XPath;
import javax.xml.xpath.XPathConstants;
import javax.xml.xpath.XPathFactory;

public class XMLExample {

    public static void run() throws Exception {
        DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance(); //создали фабрику строителей, сложный и грамосткий процесс (по реже выполняйте это действие)
        // f.setValidating(false); // не делать проверку валидации
        DocumentBuilder db = dbf.newDocumentBuilder(); // создали конкретного строителя документа
        Document doc = db.parse(new File("src/main/resources/process/claim.xml")); // стооитель построил документ
        //Document - тоже является нодом, и импленментирует методы
        visit(doc, 0);

        NodeList nodeList = doc.getElementsByTagName("stages");
        Element elem = (Element) nodeList.item(0);
        nodeList = elem.getElementsByTagName("stage");

        // http://www.ibm.com/developerworks/ru/library/x-javaxpathapi/
        // https://docs.oracle.com/javase/tutorial/jaxp/xslt/xpath.html
        // http://habrahabr.ru/post/128175/
        //XPathFactory xpathFactory = XPathFactory.newInstance();
        //XPath xpath = xpathFactory.newXPath();
        // // XPathExpression expr = xpath.compile("//book[author='Neal Stephenson']/title/text()");
        // // Object result = expr.evaluate(doc, XPathConstants.NODESET);
        //NodeList links = (NodeList) xpath.evaluate("rootNode/link", element, XPathConstants.NODESET);
        // --
        // emp.id = node.getAttributes().getNamedItem("id").getNodeValue();
        // cNode.getLastChild().getTextContent().


        int length = nodeList.getLength();
        for (int i = 0; i < length; ++i) {
            Element elStage = (Element) nodeList.item(i);
            System.out.println(elStage.getTagName() + ":" + elStage.getAttribute("name")); // el.getTextContent()
            // get human task/ui
            if (elStage.getAttribute("type") == "humantask")
            {
                NodeList nodeListUI = elStage.getElementsByTagName("ui");
                int lengthui = nodeList.getLength();
                if (lengthui > 0) {
                    Element el = (Element) nodeListUI.item(0);
                    System.out.println(el.getTagName() + ":" + el.getAttribute("task"));
                }
            }
            /*
            Element author = (Element) authors.item(j);
            NodeList children = author.getChildNodes();
            StringBuffer sb = new StringBuffer();
            for (int k = 0; k < children.getLength(); k++) {
                Node child = children.item(k);
                // really should to do this recursively
                if (child.getNodeType() == Node.TEXT_NODE) {
                sb.append(child.getNodeValue());
                }
            }
             */
        }
    }

    public static void visit(Node node, int level) {
        NodeList list = node.getChildNodes();
        for (int i = 0; i < list.getLength(); i++) {
            Node childNode = list.item(i); // текущий нод
            process(childNode, level + 1); // обработка
            visit(childNode, level + 1); // рекурсия
        }
    }

    public static void process(Node node, int level) {
        for (int i = 0; i < level; i++) {
            System.out.print('\t');
        }
        System.out.print(node.getNodeName());
        if (node instanceof Element) {
            Element e = (Element) node;
            // работаем как с элементом (у него есть атрибуты и схема)
        }
        System.out.println();
    }

}
