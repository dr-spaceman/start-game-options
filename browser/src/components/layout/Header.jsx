import React from 'react';
import TopNav from './TopNav.jsx';
import Search from './Search.jsx';
import Button from '../ui/Button.jsx';
import { QuestionBlock, LoadingMascot } from '../../lib/icons.js';

const TopNavUser = React.lazy(() => import(/* webpackChunkName: "top-nav-user" */'./TopNavUser.jsx'));
const Login = React.lazy(() => import(/* webpackChunkName: "login" */'./Login.jsx'));

function HeaderUser({ username }) {
    const [loginLoaded, setLoginLoaded] = React.useState(false);
    const lazyloadLogin = (event) => {
        event.preventDefault();
        setLoginLoaded(true);
    };

    const LoginButton = ({ handleClick }) => (
        <Button title="Login" onClick={handleClick} classes={{ 'button-header': true }}>
            <QuestionBlock />
        </Button>
    );

    return (
        <div id="login">
            {username ? (
                <React.Suspense fallback="User"><TopNavUser username={username} /></React.Suspense>
            ) : (
                <>
                    {loginLoaded
                        ? <React.Suspense fallback={<LoadingMascot />}><Login LoginButton={LoginButton} /></React.Suspense>
                        : <LoginButton handleClick={lazyloadLogin} />
                    }
                </>
            )}
        </div>
    );
}

export default function Header(props) {
    const { username } = props;

    return (
        <>
            <TopNav />
            <HeaderUser username={username} />
            <Search />
        </>
    );
}
