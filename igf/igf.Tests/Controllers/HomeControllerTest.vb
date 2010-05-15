Imports System
Imports System.Collections.Generic
Imports System.Text
Imports System.Web.Mvc
Imports Microsoft.VisualStudio.TestTools.UnitTesting
Imports igf

<TestClass()> Public Class HomeControllerTest

    <TestMethod()> Public Sub Index()
        ' Arrange
        Dim controller As HomeController = New HomeController()

        ' Act
        Dim result As ViewResult = CType(controller.Index(), ViewResult)

        ' Assert
        Dim viewData As ViewDataDictionary = result.ViewData
        Assert.AreEqual("Welcome to ASP.NET MVC!", viewData("Message"))
    End Sub

    <TestMethod()> Public Sub About()
        ' Arrange
        Dim controller As HomeController = New HomeController()

        ' Act
        Dim result As ViewResult = CType(controller.About(), ViewResult)

        ' Assert
        Assert.IsNotNull(result)
    End Sub
End Class
