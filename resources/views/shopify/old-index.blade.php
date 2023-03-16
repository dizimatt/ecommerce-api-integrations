<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>React Local</title>
    <!-- Import the React, React-Dom and Babel libraries from unpkg -->
    <script type="application/javascript" src="js/react/react.production.min.js"></script>
    <script type="application/javascript" src="js/react/react-dom.production.min.js"></script>
    <script type="application/javascript" src="js/react/babel.js"></script>
    <link rel="stylesheet" href="css/react/admin.css">
</head>

<body>
<div id="root"></div>

<script type="text/babel">
    // Create a ES6 class component

    class ConfigTable extends React.Component {

        renderAllLinks(){
            return (
                <span>
                    {this.props.links.map((link) => (
                        <button onClick={() => this.props.onClick(link.name)}>
                            {link.title}
                        </button>
                    ))}
                </span>
            );
        }

        render(){

            return (
                <table className="services-table">
                    <tr><th colSpan="3">Config Values</th></tr>
                    <tr>
                        <td>{this.renderAllLinks()}</td></tr>
                    <tr></tr>
                </table>
            );
        }
    }

    class ShopifyPage extends React.Component {
        render(){
            return (
                <div>
                    <h1>Shopify Panel</h1>
                    <p>Nothing to see here! please move along...</p>
                    <table>
                        <tr>
                            <th style={ {textAlign: 'left'} }>Shop Name</th><th style={ {textAlign: 'left'} }>id</th><th style={ {textAlign: 'left'} }>title</th>
                        </tr>
                        @foreach($shopify_products as $product)
                        <tr>
                        <td>{{store()->shop_name}}</td>
                        <td>{{$product['id']}}</td>
                        <td>{{$product['title']}}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            );
        }
    }
    class BigCommercePage extends React.Component {
        render(){
            return (
                <div>
                    <h1>BigCommerce Panel</h1>
                    <p>Nothing to see here! please move along...</p>
                    <table>
                        <tr>
                            <th style={ {textAlign: 'left'} }>Shop Name</th> <th style={ {textAlign: 'left'} }>id</th><th style={ {textAlign: 'left'} }>title</th>
                        </tr>
                        @foreach($bigcommerce_products as $product)
                        <tr>
                        <td>{{store()->shop_name}}</td>
                        <td>{{$product['id']}}</td>
                        <td>{{$product['name']}}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            );
        }
    }
    class DolibarrPage extends React.Component {
        render(){
            return (
                <div>
                    <h1>Dolibarr Panel</h1>
                    <p>Nothing to see here! please move along...</p>
                </div>
            );
        }
    }

    class Admin extends React.Component {
        renderMainPanel() {
            switch (this.state.currentPanel) {
                case 'shopify':
                    return (<ShopifyPage />);
                case 'bigcommerce':
                    return (<BigCommercePage />);
                case 'dolibarr':
                    return (<DolibarrPage />);
                default:
                    return (<h1>Admin panel hasn't been chosen yet!</h1>);
            }
        }

        constructor(props) {
            super(props);
            this.state = {
                links: [
                    {
                        title: 'Shopify',
                        'name':'shopify'
                    },
                    {
                        title: 'Bigcommerce',
                        'name':'bigcommerce'
                    },
                    {
                        title: 'Dolibarr',
                        'name':'dolibarr'
                    }
                ],
                currentPanel: 'none'
            };
        }

        handleClick(i) {
            this.setState({
                currentPanel: i
            });
        }

        render() {
            return (
                <div className="admin">
                    <div className="admin-menu">
                        <ConfigTable
                            links={this.state.links}
                            onClick={i => this.handleClick(i)}
                        />
                        <div className="mainPanel">
                            {this.renderMainPanel()}
                        </div>
                    </div>
                </div>
            );
        }
    }

    // ========================================

    /*
    const root = ReactDOM.createRoot(document.getElementById("root"));
    root.render(<Game />);
    */

    const rootElement = document.getElementById('root')

    ReactDOM.render(
        <Admin />,
        rootElement
    )


</script>

</body>

</html>
