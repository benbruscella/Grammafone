<%@ Page Language="C#" MasterPageFile="~/Views/Shared/Site.Master" Inherits="System.Web.Mvc.ViewPage" %>

<asp:Content ID="indexTitle" ContentPlaceHolderID="TitleContent" runat="server">
    Home Page
</asp:Content>

<asp:Content ID="indexContent" ContentPlaceHolderID="MainContent" runat="server">
                <div class="titlebar">
                    Gramophone.FM
                </div>
                <div class="scroll iscroll">
	                <ul class="imenu">
	                    <li><a href="buttons.html" title="Buttons" class="arrow">Buttons</a></li>
	                    <li><a href="scroll.html" title="Scroll" class="arrow">Scroll</a></li>
	                    <li><a href="tabs.html" title="Tabs" class="arrow">Tabs</a></li>
	                    <li><a href="gallery.html" title="Gallery" class="arrow">Gallery</a></li>
	                    <li><a href="menu.html" title="Menu" class="arrow">Recursion</a></li>
	                </ul>
	                <ul class="imenu">
	                    <li>
	                        <a href="#" title="Single" class="arrow icon iicon">
	                            <em class="ii-envelope"></em>
	                            Single
	                            <span>way</span>
	                        </a>
	                    </li>
	                </ul>
	                <ul class="imenu">
	                    <li><a href="#" title="Label 1">Label 1</a></li>
	                    <li><a href="#" title="Label 2">Label 2</a></li>
	                    <li>Label 3 <input class="icheckbox" type="checkbox" name="label 3" value="1"/></li>
	                </ul>
                </div>
</asp:Content>
