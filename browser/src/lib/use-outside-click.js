import React from 'react';

/**
 * Hook that alerts clicks outside of the passed ref
 */
export default function useOutsideClick(ref, callback) {
    React.useEffect(() => {
        /**
         * Alert if clicked on outside of element
         */
        function handleClickOutside(event) {
            if (ref.current && !ref.current.contains(event.target)) {
                console.log('outside click detected', callback);
                callback();
            }
        }

        // Bind the event listener
        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            // Clean up before calling the effect again on the next render
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [ref]);
}

/**
 * Example use:
 * Component that alerts if you click outside of it
 */
function MyComponent(props) {
    const wrapperRef = useRef(null);
    useOutsideClick(wrapperRef);

    return <div ref={wrapperRef}>{props.children}</div>;
}
