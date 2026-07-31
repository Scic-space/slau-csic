

import * as React from "react";
import { BellRing, MessageCircle, AlertTriangle, CheckCircle, Info } from "lucide-react";
import { router, Link } from "@inertiajs/react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

export interface NotificationItem {
  id: number;
  type?: string;
  title: string;
  message?: string;
  priority?: string;
  action_required?: boolean;
  action_url?: string | null;
  is_read?: boolean;
  created_at?: string;
  timestamp?: string;
  read?: boolean;
}

interface NotificationsProps {
  notifications?: NotificationItem[];
  icon?: React.ReactNode;
  maxHeight?: string;
}

function timeAgo(dateStr?: string) {
  if (!dateStr) return "";
  const diffMs = Date.now() - new Date(dateStr).getTime();
  const mins = Math.floor(diffMs / 60000);
  if (mins < 1) return "now";
  if (mins < 60) return `${mins}m`;
  const hrs = Math.floor(diffMs / 3600000);
  if (hrs < 24) return `${hrs}h`;
  const days = Math.floor(diffMs / 86400000);
  return `${days}d`;
}

function normalize(n: NotificationItem): NotificationItem {
  return {
    ...n,
    is_read: n.is_read ?? n.read ?? false,
    timestamp: n.timestamp ?? timeAgo(n.created_at),
  };
}

export default function Notifications({
  notifications = [],
  icon,
  maxHeight = "420",
}: NotificationsProps) {
  const items = notifications.map(normalize);
  const unreadCount = items.filter((n) => !n.is_read).length;

  const getIcon = (type?: string) => {
    switch (type) {
      case "message":
        return <MessageCircle className="w-5 h-5 text-blue-500" />;
      case "alert":
      case "high":
      case "critical":
        return <AlertTriangle className="w-5 h-5 text-amber-500" />;
      case "success":
        return <CheckCircle className="w-5 h-5 text-green-500" />;
      default:
        return <Info className="w-5 h-5 text-gray-500" />;
    }
  };

  function handleMarkRead(id: number) {
    router.post(`/notifications/${id}/read`, {}, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => window.location.reload(),
    });
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger className="relative p-2 rounded-lg border hover:border-gray-200 hover:bg-gray-100 inline-flex items-center justify-center transition-colors dark:hover:bg-gray-800 dark:hover:border-gray-700">
        {icon || <BellRing className="w-5 h-5 text-gray-700 dark:text-gray-300" />}
        {unreadCount > 0 && (
          <span className="absolute -top-1.5 -right-1.5 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full">
            {unreadCount > 99 ? "99+" : unreadCount}
          </span>
        )}
      </DropdownMenuTrigger>

      <DropdownMenuContent
        side="bottom"
        align="end"
        className="w-96 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-y-auto"
        style={{ maxHeight: `${maxHeight}px` }}
      >
        {items.length === 0 ? (
          <div className="p-6 text-center">
            <BellRing className="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
            <p className="text-sm text-gray-500 dark:text-gray-400">No notifications</p>
          </div>
        ) : (
          <div className="divide-y divide-gray-100 dark:divide-gray-800">
            <div className="flex items-center justify-between px-4 py-3">
              <h3 className="text-sm font-semibold text-gray-900 dark:text-white">
                Notifications
              </h3>
              {unreadCount > 0 && (
                <button
                  onClick={() => {
                    router.post("/notifications/read-all", {}, {
                      preserveScroll: true,
                      preserveState: true,
                      onSuccess: () => window.location.reload(),
                    });
                  }}
                  className="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                >
                  Mark all read
                </button>
              )}
            </div>
            {items.slice(0, 5).map((n) => (
              <div
                key={n.id}
                className={`flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors ${
                  n.is_read ? "" : "bg-blue-50/50 dark:bg-blue-950/20"
                }`}
              >
                <div className="mt-0.5 shrink-0">{getIcon(n.type)}</div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between gap-2">
                    <span
                      className={`text-sm truncate ${
                        n.is_read
                          ? "text-gray-600 dark:text-gray-400"
                          : "font-medium text-gray-900 dark:text-white"
                      }`}
                    >
                      {n.title}
                    </span>
                    {n.timestamp && (
                      <span className="shrink-0 text-xs text-gray-400">{n.timestamp}</span>
                    )}
                  </div>
                  {n.message && (
                    <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                      {n.message}
                    </p>
                  )}
                  <div className="mt-1.5 flex items-center gap-2">
                    {n.action_url && (
                      <Link
                        href={n.action_url}
                        className="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                      >
                        View
                      </Link>
                    )}
                    {!n.is_read && (
                      <button
                        onClick={() => handleMarkRead(n.id)}
                        className="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                      >
                        Dismiss
                      </button>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        <Link
          href="/notifications"
          className="block border-t border-gray-100 dark:border-gray-800 px-4 py-3 text-center text-xs font-medium text-indigo-600 hover:bg-gray-50 dark:text-indigo-400 dark:hover:bg-gray-800/50"
        >
          View all notifications
        </Link>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
