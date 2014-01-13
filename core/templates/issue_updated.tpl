<html>
    <head>
        <title>{{instance_title}}</title>
        <base href="{{instance_url}}" />
    </head>

    <body style="font-family: arial;">
        <div style="-webkit-border-radius: 8px; -moz-border-radius: 8px; border-radius: 8px; background-color: #F0F0F0; padding: 15px;">

            <a href="view_user_page.php?id={{user.id}}" style="text-decoration:none; color: DarkCyan;">{{user.name}}</a> has updated issue <a href="view.php?id={{id}}" style="text-decoration:none; color: DarkCyan;">{{id}}</a><br />

            <br />

            <fieldset style="-webkit-border-radius: 8px; -moz-border-radius: 8px; border-radius: 8px; background-color: white; padding: 15px;">
                <a href="view.php?id={{id}}" style="font-size: 24; font-weight: bold; text-decoration:none; color: DarkCyan;">{{subject}}</a><br />

                <br />

                <table cellpadding="5">
                    <tr valign="top">
                        <td width="60">
                            <a href="view_user_page.php?id={{reporter.id}}"><img src="{{reporter.avatar}}" style="border-radius: 50%; -moz-border-radius: 50%; -webkit-border-radius: 50%;" /></a>
                        </td>
                        <td>
                            {{description}}
                        </td>
                    </tr>
                </table>

                <br />

                <a href="view.php?id={{id}}" style="font-size: 18; font-weight: bold; text-decoration:none; color: DarkCyan;">View</a>
                -
                <a href="view.php?id={{id}}#reply" style="font-size: 18; font-weight: bold; text-decoration:none; color: DarkCyan;">Reply</a>
            </fieldset>

            <table>
                <tr>
                    <td>
                        <a href="{{mantis_product_url}}" border="0"><img src="{{mantis_avatar_48x48}}" width="24" height="24" alt="{{mantis_product_name}}" /></a>
                    </td>
                    <td>
                        <span style="font-size: 12; font-color: gray;">Sent by <a href="{{mantis_product_url}}">{{mantis_product_name}}</a> at {{timestamp}}.</span>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
