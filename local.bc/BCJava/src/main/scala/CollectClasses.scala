/**
 * http://www.michaelpollmeier.com/fun-with-scalas-new-reflection-api-2-10/
 * http://www.slideshare.net/prasinous/scaladays-2013-final
 * http://engineering.monsanto.com/2015/05/14/implicits-intro/
 */
import scala.reflect.runtime.universe._

class CollectClasses {

  val messages = {
    /*
    val mirror = runtimeMirror(this.getClass.getClassLoader)
    typeOf[GC.type].decls.collect {
      case c: ClassSymbol if c.toType <:< typeOf[GC] =>
        mirror.runtimeClass(c).asInstanceOf[Class[_ <: GC]]
    }
    */
  }

}
