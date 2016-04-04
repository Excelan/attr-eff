package xml

import scala.xml._
import java.net._
import scala.io.Source

//case class GGroup(name: String, title: String, fields: Seq[GField])

object XMLimport {

    def loadRemoteXML(url: String): Elem = {
        val xmlString = Source.fromURL(new URL(url)).mkString
        XML.loadString(xmlString)
    }

    def loadFileXML(file: String): Elem = {
        val pwd = new java.io.File(".").getCanonicalPath() //.getAbsolutePath()
        println(pwd)
        val source = scala.io.Source.fromFile(file, "utf-8")
        val xmlString = source.getLines mkString "\n" // source.mkString (slow)
        source.close()
        XML.loadString(xmlString)
    }

}
