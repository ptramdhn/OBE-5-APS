export default function Banner({ title, subtitle }) {
    return (
        <div className="flex flex-col rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-400 p-4 text-white">
            <h2 className="text-2xl font-medium leading-relaxed tracking-wide">{title}</h2>
            <p className="text-sm leading-relaxed">{subtitle}</p>
        </div>
    );
}
