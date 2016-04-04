//import org.junit.Assert;
//import org.junit.Ignore;
//import org.junit.Test;
import org.testng.annotations.*;
import org.testng.Assert;


// http://habrahabr.ru/post/120101/
// https://github.com/junit-team/junit/wiki/Exception-testing

// http://junit.org/junit-lambda.html
// http://junit.org

// http://www.scala-sbt.org/0.13/docs/Testing.html

// https://github.com/sbt/junit-interface

// most of the duplication can be pulled out into setUp and tearDown (@Before, @After) methods, so you don't need much extra code. Provided that the tests are not running so slowly that you stop running them often, it's better to waste a bit of CPU in the name of clean testing

// ordering
// TestNG since it supports running tests methods in any arbitrary order natively (and things like specifying that methods depends on groups of methods).
/*
http://testng.org/doc/documentation-main.html#test-groups
@Test(groups = "a")
public void f1() {}
@Test(groups = "a")
public void f2() {}
@Test(dependsOnGroups = "a")
public void g() {}
 */
// Junit 4.11 comes with @FixMethodOrder annotation. Instead of using custom solutions just upgrade your junit version and annotate test class with


//@Ignore
public class TestExample extends Assert {

    @Test
    public void justPlain() {
        System.out.println(">> CHECK TEST OUTPUT");
        assertEquals("abc", new String("abc"));
        //assertEquals("Here is test for Addition Result: ", 300, 300);
        assertEquals(300, 300);
    }

}