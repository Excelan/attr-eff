name := "GC3"

version := "1.0"

scalaVersion := "2.11.7"

libraryDependencies += "org.scala-lang" % "scala-reflect" % "2.11.7"

libraryDependencies += "org.glassfish" % "javax.json" % "1.0.4"

libraryDependencies += "org.postgresql" % "postgresql" % "9.3-1100-jdbc4"

libraryDependencies += "org.scala-lang.modules" %% "scala-xml" % "1.0.3"

libraryDependencies += "org.eclipse.jetty" % "jetty-server" % "9.3.7.v20160115"

libraryDependencies += "org.eclipse.jetty" % "jetty-servlet" % "9.3.7.v20160115"

libraryDependencies += "org.eclipse.jetty" % "jetty-servlets" % "9.3.7.v20160115"

libraryDependencies += "org.eclipse.jetty" % "jetty-util" % "9.3.7.v20160115"

libraryDependencies += "org.apache.odftoolkit" % "simple-odf" % "0.8.1-incubating"

libraryDependencies += "org.apache.odftoolkit" % "odfdom-java" % "0.8.10-incubating"

libraryDependencies += "org.scalacheck" %% "scalacheck" % "1.12.0"

libraryDependencies += "com.novocode" % "junit-interface" % "0.11" % Test

testOptions += Tests.Argument(TestFrameworks.JUnit, "-v")
