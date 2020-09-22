import React from 'react';

const useFocus = () => {
    const htmlElRef = React.useRef(null)
    const setFocus = () => { htmlElRef.current && htmlElRef.current.focus() }

    return [htmlElRef, setFocus]
}

export default function Login(props) {
    const { username } = props;

    const form = React.useRef();
    const handleSubmit = (event) => {
        event.preventDefault();
        console.log(form.current.username.value, form.current.password.value);
    };

    const [open, setOpen] = React.useState(false);
    const handleOpen = () => {
        setOpen(true);
    };

    React.useEffect(() => {
        if (form.current && form.current.username) {
            form.current.username.focus();
        }
    });

    const loginButton = <button type="button" onClick={handleOpen}>Login</button>;

    return (
        <>
            {username || loginButton}
            {open && (
                <>
                    <div className="modal-overlay" role="button" onClick={() => setOpen(false)} aria-hidden="true" aria-label="close" />
                    <div className="modal dark">
                        <form ref={form} onSubmit={handleSubmit}>
                            <input type="text" name="username" placeholder="Username" />
                            <input type="password" name="password" placeholder="Password" />
                            <button type="submit">Login</button>
                        </form>
                    </div>
                </>
            )}
        </>
    );
}
